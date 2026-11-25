<?php
require_once __DIR__ . '/../functions.php';

$db = getDB();
$projects = [];
$selectedCategory = sanitize($_GET['category'] ?? '');

if ($db) {
    try {
        if ($selectedCategory) {
            $stmt = $db->prepare("SELECT * FROM projects WHERE is_published = 1 AND category = ? ORDER BY is_featured DESC, id DESC");
            $stmt->execute([$selectedCategory]);
            $projects = $stmt->fetchAll();
        } else {
            $stmt = $db->query("SELECT * FROM projects WHERE is_published = 1 ORDER BY is_featured DESC, id DESC");
            $projects = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        $projects = [];
    }
}

// Fallback to verified commissioned portfolio if DB is not connected yet
if (empty($projects) && empty($selectedCategory)) {
    $projects = [
        [
            'id' => 1,
            'title' => '2.55 MW PM-KUSUM Ground Solar Plant #1',
            'slug' => '2-55-mw-pm-kusum-ground-solar-plant-1',
            'category' => 'PM-KUSUM Ground Solar',
            'location' => 'Bhiwani, Haryana',
            'capacity_kw' => 2550.00,
            'installation_year' => 2024,
            'description' => 'Grid-connected MW-scale utility power plant engineered and commissioned under Component-A of PM-KUSUM with 33kV substation tie-in.',
            'hero_image' => 'assets/projects/bhiwani-5mw-1.jpg'
        ],
        [
            'id' => 2,
            'title' => '2.55 MW Utility Ground Solar Plant #2',
            'slug' => '2-55-mw-utility-ground-solar-plant-2',
            'category' => 'PM-KUSUM Ground Solar',
            'location' => 'Kapurisar, Bikaner, Rajasthan',
            'capacity_kw' => 2550.00,
            'installation_year' => 2024,
            'description' => 'Grid-connected utility solar farm engineered in high-irradiance Bikaner desert region under 25-year JVVNL PPA contract.',
            'hero_image' => 'assets/projects/bhiwani-5mw-2.jpg'
        ],
        [
            'id' => 3,
            'title' => '325 kW RWA Housing Society Rooftop',
            'slug' => '325-kw-rwa-housing-society-rooftop',
            'category' => 'Housing Societies',
            'location' => 'CHS Complex, Maharashtra',
            'capacity_kw' => 325.00,
            'installation_year' => 2024,
            'description' => 'Multi-tower residential rooftop installation offsetting high-speed elevators, water pumps, and campus common area lighting under MSEDCL.',
            'hero_image' => 'assets/projects/industrial-shed-2.jpg'
        ],
        [
            'id' => 4,
            'title' => '170 kW Industrial Shed Solar Plant',
            'slug' => '170-kw-industrial-shed-solar-plant',
            'category' => 'Industrial Solar Sheds',
            'location' => 'Jhajjar, Haryana',
            'capacity_kw' => 170.00,
            'installation_year' => 2024,
            'description' => 'Custom non-penetrative roof structure engineering for corrugated metal factory shed minimizing daytime power tariffs under DHBVN.',
            'hero_image' => 'assets/projects/industrial-shed-1.jpg'
        ],
        [
            'id' => 5,
            'title' => '150 kW On-Grid Commercial Solar Plant',
            'slug' => '150-kw-on-grid-commercial-solar-plant',
            'category' => 'Commercial Solar',
            'location' => 'Bulandshahr, UP',
            'capacity_kw' => 150.00,
            'installation_year' => 2024,
            'description' => 'Rooftop solar installation for commercial manufacturing unit with bi-directional net metering clearance under UPPCL.',
            'hero_image' => 'assets/projects/industrial-shed-3.jpg'
        ],
        [
            'id' => 6,
            'title' => '60 kW Solar Power Installation',
            'slug' => '60-kw-solar-power-installation',
            'category' => 'Schools & Institutions',
            'location' => 'Mainpuri, UP',
            'capacity_kw' => 60.00,
            'installation_year' => 2024,
            'description' => 'High-efficiency mono-PERC rooftop solar power installation delivering clean energy independence for institutional facility.',
            'hero_image' => 'assets/projects/mainpuri-60kw.jpg'
        ],
        [
            'id' => 7,
            'title' => '40 kW Assotech Blith RWA Solar Rooftop',
            'slug' => '40-kw-assotech-blith-rwa-solar-rooftop',
            'category' => 'Housing Societies',
            'location' => 'Gurugram, Haryana',
            'capacity_kw' => 40.00,
            'installation_year' => 2024,
            'description' => 'High-rise residential rooftop solar installation offsetting common facility power bills under Haryana solar policy.',
            'hero_image' => 'assets/projects/bhiwani-ground-7.jpg'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executed Solar Projects &amp; Case Studies - JSN Renewable Energy</title>
    <meta name="description" content="Explore 5.85 MW+ of commissioned solar projects by JSN Renewable Energy across Haryana, Delhi NCR, UP, Rajasthan, and Maharashtra.">
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

    <!-- MAIN STICKY NAVBAR (SIMPLIFIED 6-ITEM NAV) -->
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

    <section class="page-banner">
        <div class="container">
            <div style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 1px; color: var(--orange); font-weight: 700; margin-bottom: 6px;">Home / Projects</div>
            <h1>Commissioned Solar Projects &amp; Case Studies</h1>
            <p>Over 5.85 MW commissioned across utility PM-KUSUM ground solar, industrial factory sheds, commercial buildings, and housing societies.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <!-- Category Filter Pills -->
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:28px;">
                <a href="index.php" class="btn <?php echo empty($selectedCategory) ? 'btn-orange' : 'btn-secondary'; ?>" style="padding:8px 16px; font-size:13px;">All Projects</a>
                <?php foreach (['PM-KUSUM Ground Solar', 'Industrial Solar Sheds', 'Commercial Solar', 'Housing Societies', 'Schools & Institutions'] as $catOpt): ?>
                    <a href="index.php?category=<?php echo urlencode($catOpt); ?>" class="btn <?php echo $selectedCategory === $catOpt ? 'btn-orange' : 'btn-secondary'; ?>" style="padding:8px 16px; font-size:13px;"><?php echo sanitize($catOpt); ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($projects)): ?>
                <div style="background:var(--background); padding:40px; border-radius:var(--radius-lg); text-align:center;">
                    <h3 style="margin-bottom:8px;">No projects found in this category</h3>
                    <p style="margin-bottom:16px;">View all commissioned solar installations across India.</p>
                    <a href="index.php" class="btn btn-orange">View All Projects</a>
                </div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($projects as $p): ?>
                        <div class="project-card">
                            <div class="project-img-wrapper">
                                <img src="<?php echo sanitize(resolveAssetPath($p['hero_image'], '../')); ?>" alt="<?php echo sanitize($p['title']); ?>" class="project-img" loading="lazy">
                                <span class="project-tag"><?php echo sanitize($p['category']); ?></span>
                            </div>
                            <div class="project-body">
                                <div class="project-meta"><?php echo sanitize($p['location']); ?><?php echo !empty($p['installation_year']) ? ' &bull; ' . (int)$p['installation_year'] : ''; ?></div>
                                <h3><?php echo sanitize($p['title']); ?></h3>
                                <p><?php echo sanitize($p['description']); ?></p>
                                <div class="project-capacity">
                                    <span><?php echo formatCapacity($p['capacity_kw']); ?></span>
                                    <a href="detail.php?slug=<?php echo urlencode($p['slug']); ?>" class="btn btn-secondary" style="padding: 6px 14px; font-size: 13px;">View Case Study</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
