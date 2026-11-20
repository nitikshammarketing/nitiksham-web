<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function nsm_uuid_ok(string $id): bool { return preg_match('/^[a-z0-9-]{1,40}$/D', $id) === 1; }
function nsm_record(array $row, bool $owner = false): array {
    $out = [
        'id' => (string) $row['id'],
        'kind' => (string) $row['kind'],
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'mime' => (string) $row['mime'],
        'published' => (bool) $row['published'],
        'createdAt' => gmdate('Y-m-d\TH:i:s\Z', strtotime($row['created_at'] . ' UTC')),
    ];
    if ($owner) {
        $out['archived'] = (bool) $row['archived'];
        $out['liveUrl'] = (string) ($row['live_url'] ?? '');
        $out['demoUrl'] = (string) ($row['demo_url'] ?? '');
    }
    return $out;
}
function nsm_list(string $filter): array {
    $where = match ($filter) {
        'gallery' => "WHERE published=1 AND archived=0 AND kind IN ('design','newsletter')",
        'software' => "WHERE published=1 AND archived=0 AND kind='software'",
        default => '',
    };
    $rows = nsm_db()->query("SELECT id,kind,title,description,mime,published,archived,live_url,demo_url,file_name,created_at FROM nitiksham_records $where ORDER BY created_at DESC")->fetchAll();
    return array_map(fn($row) => nsm_record($row, $filter === 'all'), $rows);
}
function nsm_row(string $id): ?array {
    if (!nsm_uuid_ok($id)) return null;
    $s = nsm_db()->prepare('SELECT * FROM nitiksham_records WHERE id=? LIMIT 1');
    $s->execute([$id]);
    $row = $s->fetch();
    return $row === false ? null : $row;
}
function nsm_valid_https_url($v): ?string {
    $v = trim((string) ($v ?? ''));
    if ($v === '') return '';
    $p = parse_url($v);
    if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host']) || isset($p['user']) || isset($p['pass']) || !filter_var($v, FILTER_VALIDATE_URL)) return null;
    return $v;
}
function nsm_input_json(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 262144) nsm_error('Invalid request body.', 400);
    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) nsm_error('Invalid JSON.', 400);
    return $data;
}
function nsm_limited_text($text, int $max): string {
    $text = trim((string) $text);
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}
