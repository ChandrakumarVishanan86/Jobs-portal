<?php
class Job
{
    public const PER_PAGE = 12;

    public const SALARY_RANGES = [
        '0-50000'      => 'Under 50k',
        '50000-100000' => '50k - 100k',
        '100000-200000' => '100k - 200k',
        '200000-0'     => '200k+',
    ];

    public static function categories(): array
    {
        return Database::conn()->query("SELECT DISTINCT category FROM jobs WHERE status='approved' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function locations(): array
    {
        return Database::conn()->query("SELECT DISTINCT location FROM jobs WHERE status='approved' ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);
    }

    /** @return array{jobs: array, total: int, page: int, pages: int} */
    public static function search(array $f, ?int $userId = null): array
    {
        $where = ["j.status = 'approved'"];
        $params = [];

        $q = trim($f['q'] ?? '');
        if ($q !== '') {
            $where[] = '(j.title LIKE :q1 OR c.name LIKE :q2 OR j.description LIKE :q3)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $like;
        }
        if (!empty($f['location'])) {
            $where[] = 'j.location = :loc';
            $params[':loc'] = $f['location'];
        }
        if (!empty($f['category'])) {
            $where[] = 'j.category = :cat';
            $params[':cat'] = $f['category'];
        }
        if (!empty($f['type'])) {
            $where[] = 'j.job_type = :jtype';
            $params[':jtype'] = $f['type'];
        }
        if (!empty($f['salary']) && isset(self::SALARY_RANGES[$f['salary']])) {
            [$min, $max] = array_map('intval', explode('-', $f['salary']));
            // Overlap test: job range intersects selected range (max 0 = open-ended)
            $where[] = '(j.salary_max IS NULL OR j.salary_max >= :smin)';
            $params[':smin'] = $min;
            if ($max > 0) {
                $where[] = '(j.salary_min IS NULL OR j.salary_min <= :smax)';
                $params[':smax'] = $max;
            }
        }

        $joinSaved = 'LEFT JOIN saved_jobs s ON s.job_id = j.id AND s.user_id = :uid';
        $params[':uid'] = $userId ?? 0;
        if (!empty($f['saved']) && $userId) {
            $where[] = 's.id IS NOT NULL';
        }

        $whereSql = implode(' AND ', $where);
        $pdo = Database::conn();

        $cnt = $pdo->prepare("SELECT COUNT(*) FROM jobs j JOIN companies c ON c.id = j.company_id $joinSaved WHERE $whereSql");
        $cnt->execute($params);
        $total = (int)$cnt->fetchColumn();

        $pages = max(1, (int)ceil($total / self::PER_PAGE));
        $page = min(max(1, (int)($f['page'] ?? 1)), $pages);
        $offset = ($page - 1) * self::PER_PAGE;

        $sql = "SELECT j.*, c.name AS company_name, c.logo AS company_logo,
                       (s.id IS NOT NULL) AS saved
                FROM jobs j
                JOIN companies c ON c.id = j.company_id
                $joinSaved
                WHERE $whereSql
                ORDER BY j.created_at DESC
                LIMIT " . self::PER_PAGE . " OFFSET $offset";
        $st = $pdo->prepare($sql);
        $st->execute($params);

        return ['jobs' => $st->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}
