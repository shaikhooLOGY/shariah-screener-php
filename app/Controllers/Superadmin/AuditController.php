<?php
namespace App\Controllers\Superadmin;

use PDO;

class AuditController extends BaseController
{
    public function index(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $flash = $this->takeFlash();

        $filters = [
            'actor' => trim((string)($_GET['actor'] ?? '')),
            'action' => trim((string)($_GET['action'] ?? '')),
            'entity' => trim((string)($_GET['entity'] ?? '')),
            'from' => trim((string)($_GET['from'] ?? '')),
            'to' => trim((string)($_GET['to'] ?? '')),
        ];

        $query = 'SELECT al.id, al.actor_id, al.action, al.entity, al.entity_id, al.meta, al.created_at, u.name AS actor_name
                  FROM audit_log al LEFT JOIN users u ON u.id = al.actor_id';
        $where = [];
        $params = [];
        if ($filters['actor'] !== '') {
            if (ctype_digit($filters['actor'])) {
                $where[] = 'al.actor_id = :actor_id';
                $params[':actor_id'] = (int)$filters['actor'];
            } else {
                $where[] = '(LOWER(u.name) LIKE :actor OR LOWER(u.email) LIKE :actor)';
                $params[':actor'] = '%'.mb_strtolower($filters['actor']).'%';
            }
        }
        if ($filters['action'] !== '') {
            $where[] = 'al.action = :action';
            $params[':action'] = $filters['action'];
        }
        if ($filters['entity'] !== '') {
            $where[] = 'al.entity = :entity';
            $params[':entity'] = $filters['entity'];
        }
        if ($filters['from'] !== '') {
            $where[] = 'al.created_at >= :from';
            $params[':from'] = $filters['from'];
        }
        if ($filters['to'] !== '') {
            $where[] = 'al.created_at <= :to';
            $params[':to'] = $filters['to'];
        }
        if ($where) {
            $query .= ' WHERE '.implode(' AND ', $where);
        }
        $query .= ' ORDER BY al.id DESC LIMIT 200';

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($logs as &$log) {
            $log['meta'] = $log['meta'] ? json_decode($log['meta'], true) : [];
        }

        $this->view('superadmin/audit', [
            'title' => 'Superadmin · Audit log',
            'flash' => $flash,
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    public function exportCsv(): void
    {
        $this->requireSuperadmin();
        $pdo = $this->pdo();
        $filters = [
            'bucket' => trim((string)($_GET['bucket'] ?? '')),
        ];

        $stmt = $pdo->query('SELECT al.created_at, u.name AS actor_name, al.action, al.entity, al.entity_id, al.meta
            FROM audit_log al LEFT JOIN users u ON u.id = al.actor_id ORDER BY al.id DESC LIMIT 200');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_log.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Timestamp', 'Actor', 'Action', 'Entity', 'Entity ID', 'Meta']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($out, [
                $row['created_at'],
                $row['actor_name'],
                $row['action'],
                $row['entity'],
                $row['entity_id'],
                $row['meta'],
            ]);
        }
        fclose($out);
    }
}
