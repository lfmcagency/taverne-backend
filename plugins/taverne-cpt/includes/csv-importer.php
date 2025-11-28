<?php
/**
 * CSV Term Importer/Exporter
 * Bulk import/export taxonomy terms with full metadata (title, description, thumbnail)
 * Supports all 9 plate_* taxonomies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add CSV Import/Export admin page under Plates menu
 * Bulk manage taxonomy terms with metadata (title, description, thumbnail)
 */
add_action('admin_menu', 'taverne_csv_importer_menu');

function taverne_csv_importer_menu() {
    add_submenu_page(
        'edit.php?post_type=plate',
        'Import/Export Terms',
        'Import Terms',
        'manage_options',
        'taverne-import-terms',
        'taverne_csv_importer_page'
    );
}

// Render the admin page
function taverne_csv_importer_page() {
    ?>
    <div class="wrap">
        <h1>Import/Export Taxonomy Terms</h1>
        <p>Bulk import or export terms for all plate taxonomies with full metadata.</p>
        
        <!-- Import Section -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Import Terms from CSV</h2>
            <p>Upload a CSV file with the following format:</p>
            <code>taxonomy,slug,name,description,image_url</code>
            <p style="margin-top: 10px;"><strong>Example row:</strong><br>
            <code>plate_technique,drypoint,Drypoint,"Direct needle on metal for velvety burr",https://example.com/drypoint.jpg</code></p>
            
            <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
                <?php wp_nonce_field('taverne_import_terms', 'taverne_import_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="csv_file">CSV File</label></th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <p class="description">Select a CSV file to import taxonomy terms.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="import_csv" class="button button-primary" value="Import Terms">
                </p>
            </form>
        </div>
        
        <!-- Export Section -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Export Current Terms to CSV</h2>
            <p>Download all plate_* taxonomy terms with their metadata in CSV format.</p>
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('taverne_export_terms', 'taverne_export_nonce'); ?>
                <p class="submit">
                    <input type="submit" name="export_csv" class="button button-secondary" value="Download Terms CSV">
                </p>
            </form>
        </div>
        
        <?php
        // Handle import
        if (isset($_POST['import_csv'])) {
            taverne_handle_csv_import();
        }
        
        // Handle export
        if (isset($_POST['export_csv'])) {
            taverne_handle_csv_export();
        }
        ?>
    </div>
    <?php
}

/**
 * Process CSV upload and bulk import/update taxonomy terms
 * Format: taxonomy,slug,name,description,image_url
 * Validates terms exist, downloads images to media library, shows errors
 */
function taverne_handle_csv_import() {
    // Security checks
    if (!isset($_POST['taverne_import_nonce']) || !wp_verify_nonce($_POST['taverne_import_nonce'], 'taverne_import_terms')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action');
    }
    
    // Check file upload
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>Error uploading file. Please try again.</p></div>';
        return;
    }
    
    // Validate file type
    $file_type = wp_check_filetype($_FILES['csv_file']['name']);
    if ($file_type['ext'] !== 'csv' && $_FILES['csv_file']['type'] !== 'text/csv') {
        echo '<div class="notice notice-error"><p>Invalid file type. Please upload a CSV file.</p></div>';
        return;
    }
    
    // Get the plate taxonomies
    $plate_taxonomies = [
        'plate_technique',
        'plate_medium',
        'plate_study',
        'plate_motif',
        'plate_palette',
        'plate_traces',
        'plate_matrix',
        'plate_size',
        'plate_year'
    ];
    
    // Parse CSV
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$file) {
        echo '<div class="notice notice-error"><p>Could not read the CSV file.</p></div>';
        return;
    }
    
    $row = 0;
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    $header_row = true;
    
    while (($data = fgetcsv($file, 10000, ',')) !== false) {
        $row++;
        
        // Skip header row
        if ($header_row) {
            $header_row = false;
            continue;
        }
        
        // Skip empty rows
        if (empty($data[0]) || trim($data[0]) === '') {
            continue;
        }
        
        // Ensure we have at least 4 columns (taxonomy, slug, name, description)
        if (count($data) < 4) {
            $error_count++;
            $errors[] = "Row $row: Not enough columns (need at least 4)";
            continue;
        }
        
        // Extract and sanitize data
        $taxonomy = sanitize_key(trim($data[0]));
        $slug = sanitize_title(trim($data[1]));
        $name = sanitize_text_field(trim($data[2]));
        $description = wp_kses_post(trim($data[3]));
        $image_url = isset($data[4]) ? esc_url_raw(trim($data[4])) : '';
        
        // Validate taxonomy
        if (!in_array($taxonomy, $plate_taxonomies)) {
            $error_count++;
            $errors[] = "Row $row: Invalid taxonomy '$taxonomy'";
            continue;
        }
        
        if (!taxonomy_exists($taxonomy)) {
            $error_count++;
            $errors[] = "Row $row: Taxonomy '$taxonomy' doesn't exist";
            continue;
        }
        
        // Check if term exists
        $term_exists = term_exists($slug, $taxonomy);
        
        if ($term_exists) {
            // Update existing term
            $term_id = is_array($term_exists) ? $term_exists['term_id'] : $term_exists;
            $result = wp_update_term($term_id, $taxonomy, [
                'name' => $name,
                'slug' => $slug,
                'description' => $description
            ]);
        } else {
            // Insert new term
            $result = wp_insert_term($name, $taxonomy, [
                'slug' => $slug,
                'description' => $description
            ]);
        }
        
        // Check for errors
        if (is_wp_error($result)) {
            $error_count++;
            $errors[] = "Row $row: " . $result->get_error_message();
            continue;
        }
        
        // Get term ID
        $term_id = is_array($result) ? $result['term_id'] : $result;
        
        // Handle image if provided
        if (!empty($image_url)) {
            $image_result = taverne_handle_term_image($term_id, $image_url);
            if (is_wp_error($image_result)) {
                // Log image error but don't fail the import
                $errors[] = "Row $row: Term saved but image failed - " . $image_result->get_error_message();
            }
        }
        
        $success_count++;
    }
    
    fclose($file);
    
    // Display results
    if ($success_count > 0) {
        echo '<div class="notice notice-success"><p>';
        echo '<strong>✓ Import Complete!</strong><br>';
        echo "Successfully imported/updated $success_count terms.";
        echo '</p></div>';
    }
    
    if ($error_count > 0) {
        echo '<div class="notice notice-warning"><p>';
        echo "<strong>⚠ $error_count errors occurred:</strong><br>";
        echo '<details style="margin-top: 10px;"><summary style="cursor: pointer;">Click to view errors</summary>';
        echo '<ul style="margin-top: 10px;">';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></details>';
        echo '</p></div>';
    }
}

/**
 * Download image from URL and attach to taxonomy term as thumbnail
 * Uses media_handle_sideload to add to WP media library
 * Returns attachment ID or WP_Error on failure
 */
function taverne_handle_term_image($term_id, $image_url) {
    // Check if URL is valid
    if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
        return new WP_Error('invalid_url', 'Invalid image URL');
    }
    
    // Include required files
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Download image to temp file
    $temp_file = download_url($image_url);
    
    if (is_wp_error($temp_file)) {
        return $temp_file;
    }
    
    // Prepare file array
    $file = [
        'name' => basename($image_url),
        'tmp_name' => $temp_file,
        'error' => 0,
        'size' => filesize($temp_file),
    ];
    
    // Upload to media library
    $attachment_id = media_handle_sideload($file, 0);
    
    // Clean up temp file
    @unlink($temp_file);
    
    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }
    
    // Save attachment ID as term meta
    update_term_meta($term_id, 'thumbnail_id', $attachment_id);
    
    return $attachment_id;
}

/**
 * Export all plate_* taxonomy terms to CSV download
 * Includes term metadata (name, slug, description, image URL)
 * Streams directly to browser, doesn't save file on server
 */
function taverne_handle_csv_export() {
    // Security checks
    if (!isset($_POST['taverne_export_nonce']) || !wp_verify_nonce($_POST['taverne_export_nonce'], 'taverne_export_terms')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action');
    }
    
    // Get all plate taxonomies
    $plate_taxonomies = [
        'plate_technique',
        'plate_medium',
        'plate_study',
        'plate_motif',
        'plate_palette',
        'plate_traces',
        'plate_matrix',
        'plate_size',
        'plate_year'
    ];
    
    // Set headers for CSV download
    $filename = 'taverne-terms-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write header row
    fputcsv($output, ['taxonomy', 'slug', 'name', 'description', 'image_url']);
    
    // Export terms from each taxonomy
    foreach ($plate_taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                // Get image URL
                $image_url = '';
                $attachment_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                if ($attachment_id) {
                    $image_url = wp_get_attachment_url($attachment_id);
                }
                
                // Write term data
                fputcsv($output, [
                    $term->taxonomy,
                    $term->slug,
                    $term->name,
                    $term->description,
                    $image_url
                ]);
            }
        }
    }
    
    fclose($output);
    exit;
}
