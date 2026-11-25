<?php
/**
 * JSN Renewable Energy - Dynamic Homepage Entry Point
 * Automatically renders published featured projects from MySQL DB when connected.
 */
require_once __DIR__ . '/functions.php';

$html = file_get_contents(__DIR__ . '/index.html');
$db = getDB();

if ($db && $html) {
    try {
        $stmt = $db->query("SELECT * FROM projects WHERE is_published = 1 ORDER BY is_featured DESC, id DESC LIMIT 3");
        $featured = $stmt->fetchAll();

        if (!empty($featured)) {
            $cardsHtml = '';
            foreach ($featured as $p) {
                $img = sanitize(resolveAssetPath($p['hero_image'], ''));
                $title = sanitize($p['title']);
                $cat = sanitize($p['category']);
                $loc = sanitize($p['location']);
                $desc = sanitize($p['description']);
                $cap = formatCapacity($p['capacity_kw']);
                $slug = urlencode($p['slug']);

                $cardsHtml .= <<<HTML
                <div class="project-card">
                    <div class="project-img-wrapper">
                        <img src="{$img}" alt="{$title}" class="project-img" loading="lazy">
                        <span class="project-tag">{$cat}</span>
                    </div>
                    <div class="project-body">
                        <div class="project-meta">{$loc}</div>
                        <h3>{$title}</h3>
                        <p>{$desc}</p>
                        <div class="project-capacity">
                            <span>{$cap}</span>
                            <a href="projects/detail.php?slug={$slug}" class="btn btn-secondary" style="padding: 5px 12px; font-size: 12.5px;">Case Study</a>
                        </div>
                    </div>
                </div>
HTML;
            }

            $html = preg_replace(
                '#<div class="projects-grid">.*?</div>\s*<div style="text-align: center; margin-top: 36px;">#s',
                '<div class="projects-grid">' . "\n" . $cardsHtml . "\n            </div>\n\n            <div style=\"text-align: center; margin-top: 36px;\">",
                $html,
                1
            );
        }
    } catch (Exception $e) {
        // Fallback to static verified projects in index.html
    }
}

echo $html;
?>
