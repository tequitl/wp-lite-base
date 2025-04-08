<div class="wrap wpsqlc-admin-wrap">
    <h1><?php echo esc_html__('SQLite Console', 'wp-sqlite-console'); ?></h1>
    
    <div class="wpsqlc-container">
        <!-- Left Column: Database Structure Panel -->
        <div class="wpsqlc-left-column">
            <div class="wpsqlc-structure-panel">
                <h2><?php echo esc_html__('Database Structure', 'wp-sqlite-console'); ?></h2>
                <div id="wpsqlc-table-list" class="wpsqlc-table-list">
                    <div class="wpsqlc-loading">Loading database structure...</div>
                </div>
            </div>
        </div>

        <!-- Middle Column: Query Editor and Results -->
        <div class="wpsqlc-middle-column">
            <div class="wpsqlc-editor-section">
                <h2><?php echo esc_html__('SQL Query Editor', 'wp-sqlite-console'); ?></h2>
                <div class="wpsqlc-editor-container">
                    <textarea id="wpsqlc-query-editor" class="wpsqlc-editor" rows="10"></textarea>
                    <div class="wpsqlc-editor-buttons">
                        <button id="wpsqlc-execute-btn" class="button button-primary">
                            <?php echo esc_html__('Execute Query', 'wp-sqlite-console'); ?>
                        </button>
                        <button id="wpsqlc-clear-btn" class="button">
                            <?php echo esc_html__('Clear', 'wp-sqlite-console'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="wpsqlc-results-section">
                <h2><?php echo esc_html__('Query Results', 'wp-sqlite-console'); ?></h2>
                <div id="wpsqlc-results-container" class="wpsqlc-results-container">
                    <div id="wpsqlc-results-message" class="notice notice-info inline">
                        <p><?php echo esc_html__('Execute a query to see results', 'wp-sqlite-console'); ?></p>
                    </div>
                    <div id="wpsqlc-results-table" class="wpsqlc-results-table"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Database Info Section -->
        <div class="wpsqlc-right-column">
            <div class="wpsqlc-info-panel">
                <h2><?php echo esc_html__('Database Information', 'wp-sqlite-console'); ?></h2>
                <?php
                $db_file = WP_CONTENT_DIR . '/database/.ht.sqlite';
                $db_size = file_exists($db_file) ? size_format(filesize($db_file)) : 'N/A';
                ?>
                <table class="widefat">
                    <tr>
                        <th><?php echo esc_html__('File Size:', 'wp-sqlite-console'); ?></th>
                        <td><?php echo esc_html($db_size); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Query Editor Section
        <div class="wpsqlc-editor-section">
            <h2><?php echo esc_html__('SQL Query Editor', 'wp-sqlite-console'); ?></h2>
            <div class="wpsqlc-editor-container">
                <textarea id="wpsqlc-query-editor" class="wpsqlc-editor" rows="10"></textarea>
                <div class="wpsqlc-editor-buttons">
                    <button id="wpsqlc-execute-btn" class="button button-primary">
                        <?php echo esc_html__('Execute Query', 'wp-sqlite-console'); ?>
                    </button>
                    <button id="wpsqlc-clear-btn" class="button">
                        <?php echo esc_html__('Clear', 'wp-sqlite-console'); ?>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Results Section 
        <div class="wpsqlc-results-section">
            <h2><?php echo esc_html__('Query Results', 'wp-sqlite-console'); ?></h2>
            <div id="wpsqlc-results-container" class="wpsqlc-results-container">
                <div id="wpsqlc-results-message" class="notice notice-info inline">
                    <p><?php echo esc_html__('Execute a query to see results', 'wp-sqlite-console'); ?></p>
                </div>
                <div id="wpsqlc-results-table" class="wpsqlc-results-table"></div>
            </div>
        </div>-->
    </div>
</div>

<style>
.wpsqlc-admin-wrap {
    margin: 20px;
}

.wpsqlc-container {
    display: grid;
    grid-template-columns: 250px 1fr 300px;
    gap: 20px;
    max-width: 100%;
    margin: 0 auto;
}

.wpsqlc-left-column,
.wpsqlc-middle-column,
.wpsqlc-right-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.wpsqlc-editor-section {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    flex: 0 0 auto;
    min-height: 250px;
}

.wpsqlc-results-section {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    flex: 1 1 auto;
    min-height: 400px;
    overflow: auto;
}

.wpsqlc-editor-container {
    margin-bottom: 15px;
}

.CodeMirror {
    height: 150px !important;
}
.wpsqlc-structure-panel,
.wpsqlc-info-panel {
    height: fit-content;
}

.wpsqlc-info-panel {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.wpsqlc-editor-section {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.wpsqlc-editor-container {
    margin-bottom: 15px;
}

.wpsqlc-editor {
    width: 100%;
    min-height: 200px;
    font-family: monospace;
    margin-bottom: 10px;
}

.wpsqlc-editor-buttons {
    display: flex;
    gap: 10px;
}

.wpsqlc-results-section {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.wpsqlc-results-container {
    overflow-x: auto;
}

.wpsqlc-results-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.wpsqlc-results-table th,
.wpsqlc-results-table td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}

.wpsqlc-results-table th {
    background-color: #f5f5f5;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize CodeMirror
    var editor = wp.CodeMirror.fromTextArea(document.getElementById('wpsqlc-query-editor'), {
        mode: 'text/x-sql',
        theme: 'default',
        lineNumbers: true,
        lineWrapping: true,
        indentUnit: 4,
        scrollbarStyle: 'native'
    });

    // Execute Query Button Click Handler
    $('#wpsqlc-execute-btn').on('click', function() {
        var query = editor.getValue();
        if (!query.trim()) {
            alert('Please enter a SQL query');
            return;
        }

        $(this).prop('disabled', true);
        $('#wpsqlc-results-message').html('<p>Executing query...</p>').show();
        $('#wpsqlc-results-table').empty();

        $.ajax({
            url: wpsqlc.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsqlc_execute_query',
                nonce: wpsqlc.nonce,
                query: query
            },
            success: function(response) {
                if (response.success) {
                    var results = response.data.result;
                    if (Array.isArray(results) && results.length > 0) {
                        var table = $('<table></table>');
                        
                        // Create header
                        var thead = $('<thead></thead>');
                        var headerRow = $('<tr></tr>');
                        Object.keys(results[0]).forEach(function(key) {
                            headerRow.append($('<th></th>').text(key));
                        });
                        thead.append(headerRow);
                        table.append(thead);

                        // Create body
                        var tbody = $('<tbody></tbody>');
                        results.forEach(function(row) {
                            var tr = $('<tr></tr>');
                            Object.values(row).forEach(function(value) {
                                tr.append($('<td></td>').text(value));
                            });
                            tbody.append(tr);
                        });
                        table.append(tbody);

                        $('#wpsqlc-results-message').hide();
                        $('#wpsqlc-results-table').html(table);
                    } else {
                        $('#wpsqlc-results-message')
                            .removeClass('notice-error')
                            .addClass('notice-success')
                            .html('<p>Query executed successfully. No results to display.</p>')
                            .show();
                    }
                } else {
                    $('#wpsqlc-results-message')
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p>Error: ' + (response.data || 'Unknown error') + '</p>')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                $('#wpsqlc-results-message')
                    .removeClass('notice-success')
                    .addClass('notice-error')
                    .html('<p>Error: ' + error + '</p>')
                    .show();
            },
            complete: function() {
                $('#wpsqlc-execute-btn').prop('disabled', false);
            }
        });
    });

    // Clear Button Click Handler
    $('#wpsqlc-clear-btn').on('click', function() {
        editor.setValue('');
        $('#wpsqlc-results-message')
            .removeClass('notice-error notice-success')
            .addClass('notice-info')
            .html('<p>Execute a query to see results</p>')
            .show();
        $('#wpsqlc-results-table').empty();
    });
});
</script>