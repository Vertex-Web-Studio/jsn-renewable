<?php
require_once __DIR__ . '/../functions.php';

$db = getDB();
$projects = [];

if ($db) {
    $stmt = $db->query("SELECT * FROM projects WHERE is_published = 1 ORDER BY is_featured DESC, id DESC");
    $projects = $stmt->fetchAll();
}

// Fallback to static items if DB has no published items yet
if (empty($projects)) {
    $projects = [
        [
            'id' => 1,
            'title' => '2.55 MW Utility Ground Plant #1',
            'slug' => '2-55-mw-pm-kusum-ground-solar-plant-1',
            'category' => 'PM-KUSUM Ground Solar',
            'location' => 'Bhiwani, Haryana',
            'capacity_kw' => 2550.00,
            'description' => 'Grid-connected MW-scale utility power plant engineered and commissioned under federal PM-KUSUM program in Bhiwani, Haryana.',
            'hero_image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?q=80&w=800&auto=format&fit=crop'
        ],
        [
            'id' => 2,
            'title' => '2.55 MW Utility Ground Solar Plant #2',
            'slug' => '2-55-mw-utility-ground-solar-plant-2',
            'category' => 'PM-KUSUM Ground Solar',
            'location' => 'Rajasthan / NCR',
            'capacity_kw' => 2550.00,
            'description' => 'MW-scale utility grid project commissioned with full substation synchronization and 25-year DISCOM Power Purchase Agreement (PPA).',
            'hero_image' => 'https://images.unsplash.com/photo-1548337138-e87d889cc369?q=80&w=800&auto=format&fit=crop'
        ],
        [
            'id' => 3,
            'title' => '170 kW Industrial Shed Solar Plant',
            'slug' => '170-kw-industrial-shed-solar-plant',
            'category' => 'Industrial Solar Sheds',
            'location' => 'Jhajjar, Haryana',
            'capacity_kw' => 170.00,
            'description' => 'Custom roof structure engineering for corrugated metal factory shed minimizing electricity tariffs under DHBVN net-metering.',
            'hero_image' => 'https://images.unsplash.com/photo-1613665813446-82a78c468a1d?q=80&w=800&auto=format&fit=crop'
        ],
        [
            'id' => 4,
            'title' => '325 kW Housing Society Rooftop Plant',
            'slug' => '325-kw-housing-society-rooftop-plant',
            'category' => 'Housing Societies',
            'location' => 'Maharashtra',
            'capacity_kw' => 325.00,
            'description' => 'Large-scale multi-tower residential rooftop solar setup providing clean power for common elevators, lighting, and water pumps.',
            'hero_image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?q=80&w=800&auto=format&fit=crop'
        ],
        [
            'id' => 5,
            'title' => '40 kW Assotech Blith RWA Rooftop Plant',
            'slug' => '40-kw-assotech-blith-rwa-rooftop-plant',
            'category' => 'Housing Societies',
            'location' => 'Gurugram, Haryana',
            'capacity_kw' => 40.00,
            'description' => 'On-grid solar installation offsetting high-rise common facility electricity tariffs under Haryana solar policy guidelines.',
            'hero_image' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?q=80&w=800&auto=format&fit=crop'
        ],
        [
            'id' => 6,
            'title' => '110 kW Commercial Building Solar Plant',
            'slug' => '110-kw-commercial-building-solar-plant',
            'category' => 'Commercial Solar',
            'location' => 'Ghaziabad, UP',
            'capacity_kw' => 110.00,
            'description' => 'On-grid net metered rooftop solution drastically reducing daytime peak corporate office power expenses in Ghaziabad.',
            'hero_image' => 'https://images.unsplash.com/photo-1559302504-64aae6ca6b6d?q=80&w=800&auto=format&fit=crop'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSN Renewable Energy - Executed Projects & Case Studies</title>
    <!-- Favicon & Tab Bar Icon -->
    <link rel="icon" type="image/svg+xml" href="../assets/logo.svg">
    <link rel="shortcut icon" href="../assets/logo.svg">
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
                    <span style="font-size: 12px; color: #CBD5E1;" class="hide-mobile">Serving Haryana, Delhi NCR, UP, Rajasthan & Maharashtra</span>
                </div>
                <div class="top-bar-info">
                    <a href="tel:+919289044454"><i data-lucide="phone" style="width:14px; height:14px;"></i> +91 92890 44454</a>
                    <a href="mailto:info@jsnrenewable.com" class="hide-mobile"><i data-lucide="mail" style="width:14px; height:14px;"></i> info@jsnrenewable.com</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-flex">
                <a href="../index.html" class="brand-logo">
                    <img src="../assets/logo.svg" alt="JSN Renewable Energy Logo" class="brand-logo-img">
                </a>
                <a href="../contact/index.html" class="btn btn-orange">Request Free Site Survey</a>
            </div>
        </div>
    </header>

    <section class="page-banner">
        <div class="container">
            <div style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 1px; color: var(--orange); font-weight: 700; margin-bottom: 6px;">Home / Projects</div>
            <h1>Executed Projects & Case Studies</h1>
            <p>Over 5.85 MW commissioned across major utility and commercial installations in Haryana, Delhi NCR, UP & Maharashtra.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="projects-grid">

                <?php foreach ($projects as $p): ?>
                    <div class="project-card">
                        <div class="project-img-wrapper">
                            <img src="<?php echo sanitize($p['hero_image']); ?>" alt="<?php echo sanitize($p['title']); ?>" class="project-img">
                            <span class="project-tag"><?php echo sanitize($p['category']); ?></span>
                        </div>
                        <div class="project-body">
                            <div class="project-meta"><?php echo sanitize($p['location']); ?></div>
                            <h3><?php echo sanitize($p['title']); ?></h3>
                            <p><?php echo sanitize($p['description']); ?></p>
                            <div class="project-capacity">
                                <span><?php echo number_format($p['capacity_kw'], $p['capacity_kw'] >= 1000 ? 2 : 0); ?> <?php echo $p['capacity_kw'] >= 1000 ? 'MW' : 'kW'; ?></span>
                                <a href="detail.php?slug=<?php echo urlencode($p['slug']); ?>" class="btn btn-secondary" style="padding: 6px 14px; font-size: 13px;">View Case Study</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div style="margin-bottom:12px;">
                        <img src="../assets/logo.svg" alt="JSN Renewable Energy Logo" class="footer-logo-img">
                    </div>
                    <p>JSN Renewable Energy Private Limited is a premier commercial, industrial, and ground-mounted solar EPC contractor based in Ghaziabad, UP.</p>
                    <div style="font-weight: 700; color: var(--orange); margin-top: 10px; font-size: 12px; letter-spacing:1px; text-transform:uppercase;">
                        A NEW WAY TO POWER
                    </div>
                </div>
                <div class="footer-column">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="../index.html">Home</a></li>
                        <li><a href="../solutions/index.html">Solar Solutions</a></li>
                        <li><a href="../services/index.html">Solar Services</a></li>
                        <li><a href="index.html">Executed Projects</a></li>
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
                    <a href="../contact/index.html">Privacy Policy</a>
                    <a href="../contact/index.html">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
