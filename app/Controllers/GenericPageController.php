<?php
namespace App\Controllers;

use Core\Controller;

class GenericPageController extends Controller
{
    public function home(): void {
        $this->view('pages/home', ['title' => 'Home']);
    }

    public function explore(): void {
        $this->view('pages/explore', ['title' => 'Explore']);
    }

    public function companies(): void {
        $this->view('pages/companies', ['title' => 'Companies']);
    }

    public function methodology(): void {
        $this->view('pages/methodology', ['title' => 'Methodology']);
    }

    public function standards(): void {
        $this->view('pages/standards', ['title' => 'Standards']);
    }

    public function caseStudies(): void {
        $this->view('pages/case-studies', ['title' => 'Case Studies']);
    }

    public function faq(): void {
        $this->view('pages/faq', ['title' => 'FAQ']);
    }

    public function glossary(): void {
        $this->view('pages/glossary', ['title' => 'Glossary']);
    }

    public function about(): void {
        $this->view('pages/about', ['title' => 'About']);
    }

    public function contact(): void {
        $this->view('pages/contact', ['title' => 'Contact']);
    }

    public function terms(): void {
        $this->view('pages/terms', ['title' => 'Terms']);
    }

    public function privacy(): void {
        $this->view('pages/privacy', ['title' => 'Privacy']);
    }

    public function disclaimer(): void {
        $this->view('pages/disclaimer', ['title' => 'Disclaimers']);
    }

    public function purification(): void {
        $this->view('pages/purification', ['title' => 'Purification Guide']);
    }

    public function scholars(): void {
        $this->view('pages/scholars', ['title' => 'Scholar Board']);
    }

    public function scholarProfile(string $slug): void {
        $this->view('scholars/profile', ['title' => 'Scholar Profile', 'slug' => $slug]);
    }

    public function learn(): void {
        $this->view('pages/learn', ['title' => 'Learn']);
    }

    public function articles(): void {
        $this->view('pages/articles', ['title' => 'Articles']);
    }

    public function articleShow(string $slug): void {
        $this->view('articles/show', ['title' => 'Article', 'slug' => $slug]);
    }

    public function login(): void {
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function register(): void {
        $this->view('auth/register', ['title' => 'Register']);
    }

    public function forgot(): void {
        $this->view('auth/forgot', ['title' => 'Forgot Password']);
    }

    public function ulamaDashboard(): void {
        $this->view('dashboard/ulama/index', ['title' => 'Ulama Dashboard']);
    }

    public function ulamaReviews(): void {
        $this->view('dashboard/ulama/reviews', ['title' => 'Ulama Reviews']);
    }

    public function adminDashboard(): void {
        $this->view('dashboard/admin/index', ['title' => 'Admin Dashboard']);
    }

    public function adminCompanies(): void {
        $this->view('dashboard/admin/companies', ['title' => 'Admin Companies']);
    }

    public function adminFilings(): void {
        $this->view('dashboard/admin/filings', ['title' => 'Admin Filings']);
    }

    public function adminUsers(): void {
        $this->view('dashboard/admin/users', ['title' => 'Admin Users']);
    }

    public function adminSettings(): void {
        $this->view('dashboard/admin/settings', ['title' => 'Admin Settings']);
    }

    public function discussions(): void {
        $this->view('pages/discussions', ['title' => 'Discussions']);
    }

    public function suggestRatios(): void {
        $this->view('pages/suggest-ratios', ['title' => 'Suggest Ratios']);
    }
}
