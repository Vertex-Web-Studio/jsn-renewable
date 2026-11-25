<?php
require_once __DIR__ . '/../functions.php';

$db = getDB();
$slug = sanitize($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$project = null;
$galleryImages = [];

if ($db) {
    try {
        if ($slug) {
            $stmt = $db->prepare("SELECT * FROM projects WHERE slug = ? AND is_published = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $project = $stmt->fetch();
        } elseif ($id) {
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND is_published = 1 LIMIT 1");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
        }
    } catch (Exception $e) {
        $project = null;
    }
}

if (!$project) {
    $project = [
        'id' => 1,
        'title' => '2.55 MW PM-KUSUM Ground Solar Plant #1',
        'slug' => '2-55-mw-pm-kusum-ground-solar-plant-1',
        'category' => 'PM-KUSUM Ground Solar',
        'location' => 'Bhiwani, Haryana',
        'client_name' => 'Utility DISCOM Grid',
        'system_type' => 'Ground-Mounted Solar',
        'capacity_kw' => 2550.00,
        'completion_date' => '2024-03-15',
        'installation_year' => 2024,
        'description' => 'Grid-connected MW-scale utility power plant engineered and commissioned under Component-A of PM-KUSUM in Bhiwani, Haryana. The plant features ALMM-listed mono-PERC bifacial solar modules, central inverters, 33kV outdoor substation tie-in, and a 25-year DISCOM Power Purchase Agreement (PPA).',
        'hero_image' => 'assets/projects/bhiwani-5mw-1.jpg',
        'seo_title' => '',
        'seo_description' => ''
    ];
} else {
    $imgStmt = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
    $imgStmt->execute([$project['id']]);
    $galleryImages = $imgStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($project['seo_title'] ?: $project['title']); ?> - JSN Renewable Energy</title>
    <meta name="description" content="<?php echo sanitize($project['seo_description'] ?: $project['description']); ?>">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <link rel="shortcut icon" href="../assets/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- TOP ANNOUNCEMENT BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-flex">
                <div class="top-bar-info">
                    <span class="badge-discom">
                        <i data-lucide="shield-check" style="width:14px; height:14px;"></i>
                        DISCOM Approved Solar EPC
                    </span>
                    <span style="font-size: 12px; color: #CBD5E1;" class="hide-mobile">Serving Haryana, Delhi NCR, UP, Rajasthan &amp; Maharashtra</span>
                </div>
                <div class="top-bar-info">
                    <a href="tel:+919289044454"><i data-lucide="phone" style="width:14px; height:14px;"></i> +91 92890 44454</a>
                    <a href="mailto:info@jsnrenewable.com" class="hide-mobile"><i data-lucide="mail" style="width:14px; height:14px;"></i> info@jsnrenewable.com</a>
                    <a href="https://wa.me/919289044454" target="_blank" style="color: var(--orange); font-weight:700;"><i data-lucide="message-square" style="width:14px; height:14px;"></i> WhatsApp</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-flex">
                <a href="../index.html" class="brand-logo" aria-label="JSN Renewable Energy Homepage">
                    <img src="../assets/logo.png" alt="JSN Renewable Energy Logo" class="brand-logo-img">
                </a>

                <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle Navigation Menu">
                    <i data-lucide="menu" style="width:26px; height:26px;"></i>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item"><a href="../index.html" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="../about/index.html" class="nav-link">About Us</a></li>
                    <li class="nav-item">
                        <a href="../solutions/index.html" class="nav-link">
                            Solutions <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="../solutions/residential-solar.html" class="dropdown-link">Residential Solar</a></li>
                            <li><a href="../solutions/commercial-solar.html" class="dropdown-link">Commercial Solar</a></li>
                            <li><a href="../solutions/industrial-solar.html" class="dropdown-link">Industrial Solar</a></li>
                            <li><a href="../solutions/rooftop-solar.html" class="dropdown-link">Rooftop Solar</a></li>
                            <li><a href="../systems/ground-mounted-solar.html" class="dropdown-link">Ground Mounted Solar</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="index.php" class="nav-link active">Projects</a></li>
                    <li class="nav-item"><a href="../calculator.html" class="nav-link">Calculator</a></li>
                    <li class="nav-item"><a href="../contact/index.html" class="nav-link">Contact</a></li>
                </ul>

                <div class="hide-mobile" style="margin-left: 15px;">
                    <a href="../contact/index.html" class="btn btn-orange">Get a Quote</a>
                </div>
            </div>
        </div>
    </header>

    <!-- PAGE BANNER -->
    <section class="page-banner">
        <div class="container">
            <div style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 1px; color: var(--orange); font-weight: 700; margin-bottom: 6px;">
                <a href="../index.html" style="color:inherit; text-decoration:none;">Home</a> / <a href="index.php" style="color:inherit; text-decoration:none;">Projects</a> / Case Study
            </div>
            <h1><?php echo sanitize($project['title']); ?></h1>
            <p><?php echo formatCapacity($project['capacity_kw']); ?> &bull; <?php echo sanitize($project['location']); ?></p>
        </div>
    </section>

    <!-- PROJECT DETAIL CONTENT -->
    <section class="section-padding">
        <div class="container">
            <div class="two-col-grid">
                <div>
                    <div style="margin-bottom:20px;">
                        <img src="<?php echo sanitize(resolveAssetPath($project['hero_image'], '../')); ?>" alt="<?php echo sanitize($project['title']); ?>" style="width:100%; height:380px; object-fit:cover; border-radius:var(--radius-lg); box-shadow:var(--shadow-md);">
                    </div>

                    <span class="eyebrow-text">CASE STUDY OVERVIEW</span>
                    <h2>Engineering &amp; Execution Highlights</h2>
                    <p style="font-size: 16px; line-height: 1.8; color: var(--text-primary);"><?php echo nl2br(sanitize($project['description'])); ?></p>

                    <?php if (!empty($galleryImages)): ?>
                        <h3 style="margin-top:30px; margin-bottom:16px;">Project Gallery</h3>
                        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
                            <?php foreach ($galleryImages as $gImg): ?>
                                <div>
                                    <img src="<?php echo sanitize(resolveAssetPath($gImg['image_url'], '../')); ?>" alt="<?php echo sanitize($gImg['alt_text'] ?: $project['title']); ?>" loading="lazy" style="width:100%; height:150px; object-fit:cover; border-radius:var(--radius-md); box-shadow:var(--shadow-sm);">
                                    <?php if (!empty($gImg['caption'])): ?>
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;"><?php echo sanitize($gImg['caption']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PROJECT SPECS SIDEBAR -->
                <div>
                    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 20px; color: var(--primary-dark); border-bottom:1px solid var(--border-light); padding-bottom:10px;">Project Technical Specs</h3>

                        <div style="display:flex; flex-direction:column; gap:14px; font-size:14px; margin-bottom:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--text-secondary);">Installed Capacity:</span>
                                <strong style="color:var(--orange); font-size:18px; font-family:var(--font-heading);"><?php echo formatCapacity($project['capacity_kw']); ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--text-secondary);">Project Category:</span>
                                <strong><?php echo sanitize($project['category']); ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--text-secondary);">System Architecture:</span>
                                <strong><?php echo sanitize($project['system_type']); ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:var(--text-secondary);">Location:</span>
                                <strong><?php echo sanitize($project['location']); ?></strong>
                            </div>
                            <?php if (!empty($project['client_name'])): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="color:var(--text-secondary);">Client Entity:</span>
                                    <strong><?php echo sanitize($project['client_name']); ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['installation_year']) || !empty($project['completion_date'])): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="color:var(--text-secondary);">Commissioned:</span>
                                    <strong><?php echo !empty($project['completion_date']) ? date('F Y', strtotime($project['completion_date'])) : (int)$project['installation_year']; ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a href="../contact/index.html" class="btn btn-orange" style="width:100%; text-align:center;">Request Similar Solar Proposal</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div style="margin-bottom:12px;">
                        <img src="../assets/logo.png" alt="JSN Renewable Energy Logo" class="footer-logo-img">
                    </div>
                    <p>JSN Renewable Energy Private Limited is a premier commercial, industrial, and ground-mounted solar EPC contractor based in Ghaziabad, UP.</p>
                    <div style="font-weight: 700; color: var(--orange); margin-top: 10px; font-size: 12px; letter-spacing:1px; text-transform:uppercase;">
                        A new way to power
                    </div>
                </div>
                <div class="footer-column">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="../index.html">Home</a></li>
                        <li><a href="../about/index.html">About Us</a></li>
                        <li><a href="../solutions/index.html">Solutions</a></li>
                        <li><a href="index.php">Projects</a></li>
                        <li><a href="../calculator.html">Calculator</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h5>Contact Head Office</h5>
                    <p style="font-size: 13px; color: #94A3B8; margin-bottom: 8px;">Plot No. 86, Kajipura, Near LK International School, Wave City, Ghaziabad, UP-201002</p>
                    <p style="font-size: 13px; color: #94A3B8;">Phone: +91 92890 44454<br>Email: info@jsnrenewable.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <div>© 2026 JSN Renewable Energy Private Limited. All rights reserved.</div>
                <div class="footer-legal-links">
                    <a href="../privacy-policy.html">Privacy Policy</a>
                    <a href="../terms-and-conditions.html">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
