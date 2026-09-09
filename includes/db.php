<?php
// includes/db.php
// SQLite-backed data layer for КИТАЙЦІ SHOP.
// Works out of the box on any shared PHP hosting with pdo_sqlite enabled (no MySQL setup needed).

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dbPath = __DIR__ . '/../data/shop.sqlite';
    $isNew = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        install_schema($pdo);
        seed_data($pdo);
    }

    return $pdo;
}

function install_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            sort_order INTEGER DEFAULT 0
        );

        CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL REFERENCES categories(id),
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            description TEXT DEFAULT '',
            price REAL NOT NULL,
            discount_price REAL,
            image TEXT DEFAULT '',
            is_active INTEGER DEFAULT 1,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE product_variants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
            size TEXT DEFAULT '',
            color TEXT DEFAULT '',
            stock INTEGER DEFAULT 0
        );

        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            phone TEXT NOT NULL,
            city TEXT DEFAULT '',
            np_branch TEXT DEFAULT '',
            comment TEXT DEFAULT '',
            items_json TEXT NOT NULL,
            total REAL NOT NULL,
            status TEXT DEFAULT 'new',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL
        );
    ");
}

function seed_data(PDO $pdo): void {
    $cats = [
        ['Одяг', 'odyag', 1],
        ['Взуття', 'vzuttya', 2],
        ['Аксесуари', 'aksesuary', 3],
    ];
    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)');
    foreach ($cats as $c) $stmt->execute($c);

    $catIds = $pdo->query('SELECT id, slug FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    // slug => id map (fetch differently)
    $catMap = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $catMap[$row['slug']] = $row['id'];
    }

    $products = [
        ['Оверсайз худі "Basic"', 'oversize-hudi-basic', $catMap['odyag'], 1290, 990,
         'Щільний флісовий худі вільного крою. М’який начіс, не втрачає форму після прання.',
         [['size'=>'S','color'=>'Чорний','stock'=>5],['size'=>'M','color'=>'Чорний','stock'=>8],['size'=>'L','color'=>'Чорний','stock'=>4],['size'=>'M','color'=>'Бежевий','stock'=>6]]],
        ['Кросівки "Retro Runner"', 'krosivky-retro-runner', $catMap['vzuttya'], 2390, null,
         'Класичні кросівки в ретро-стилі на світлій підошві. Натуральна замша + сітка.',
         [['size'=>'41','color'=>'Білий','stock'=>3],['size'=>'42','color'=>'Білий','stock'=>7],['size'=>'43','color'=>'Білий','stock'=>5],['size'=>'42','color'=>'Чорний','stock'=>2]]],
        ['Сумка-шопер "Daily"', 'sumka-shoper-daily', $catMap['aksesuary'], 890, 690,
         'Місткий шопер з щільної тканини, витримує до 15 кг. Внутрішня кишеня на блискавці.',
         [['size'=>'One size','color'=>'Чорний','stock'=>12],['size'=>'One size','color'=>'Оливковий','stock'=>9]]],
        ['Кепка "Street Line"', 'kepka-street-line', $catMap['aksesuary'], 590, null,
         'Бейсболка з вигнутим козирком, регулювання ззаду. Щільна бавовна.',
         [['size'=>'One size','color'=>'Чорний','stock'=>15],['size'=>'One size','color'=>'Хакі','stock'=>10]]],
        ['Кардиган в’язаний', 'kardygan-vyazanyi', $catMap['odyag'], 1590, 1290,
         'М’який в’язаний кардиган, вільний крій, дві накладні кишені.',
         [['size'=>'S','color'=>'Молочний','stock'=>4],['size'=>'M','color'=>'Молочний','stock'=>6],['size'=>'L','color'=>'Молочний','stock'=>3]]],
        ['Черевики "Trek"', 'cherevyky-trek', $catMap['vzuttya'], 2790, null,
         'Утеплені черевики для холодної погоди, протектор для слизьких поверхонь.',
         [['size'=>'40','color'=>'Чорний','stock'=>6],['size'=>'41','color'=>'Чорний','stock'=>6],['size'=>'42','color'=>'Чорний','stock'=>4]]],
    ];

    $pStmt = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, price, discount_price, image) VALUES (?,?,?,?,?,?,?)');
    $vStmt = $pdo->prepare('INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)');

    foreach ($products as $p) {
        [$name, $slug, $catId, $price, $discount, $desc, $variants] = $p;
        $pStmt->execute([$catId, $name, $slug, $desc, $price, $discount, '']);
        $pid = $pdo->lastInsertId();
        foreach ($variants as $v) {
            $vStmt->execute([$pid, $v['size'], $v['color'], $v['stock']]);
        }
    }

    // Default admin login — change immediately after first deploy.
    $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')
        ->execute(['admin', password_hash('kytaitsi2026', PASSWORD_DEFAULT)]);
}

function slugify(string $text): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ie','ж'=>'zh',
        'з'=>'z','и'=>'y','і'=>'i','ї'=>'i','й'=>'i','к'=>'k','л'=>'l','м'=>'m','н'=>'n',
        'о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts',
        'ч'=>'ch','ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'iu','я'=>'ia',
    ];
    $text = mb_strtolower($text, 'UTF-8');
    $out = '';
    foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        $out .= $map[$ch] ?? $ch;
    }
    $out = preg_replace('/[^a-z0-9]+/', '-', $out);
    $out = trim($out, '-');
    return $out ?: 'item-' . substr(md5((string)microtime(true)), 0, 6);
}

function unique_slug(PDO $pdo, string $table, string $base, ?int $excludeId = null): string {
    $slug = slugify($base);
    $original = $slug;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM $table WHERE slug = ?" . ($excludeId ? " AND id != ?" : '');
        $stmt = $pdo->prepare($sql);
        $params = [$slug];
        if ($excludeId) $params[] = $excludeId;
        $stmt->execute($params);
        if (!$stmt->fetch()) return $slug;
        $slug = $original . '-' . $i;
        $i++;
    }
}

function money(float $n): string {
    return number_format($n, 0, ',', ' ') . ' грн';
}
