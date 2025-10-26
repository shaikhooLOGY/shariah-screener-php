<?php
return ['max_mb'=>(int)($_ENV['UPLOAD_MAX_MB']??10), 'allowed'=>['pdf','png','jpg','jpeg']];
