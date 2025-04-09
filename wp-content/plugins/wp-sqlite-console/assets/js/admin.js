(function($) {
    'use strict';

    // Store CodeMirror instance
    let sqlEditor = null;
    let dbStructure = null;

    // Initialize the SQLite Console
    function initSQLiteConsole() {
        // Load database structure
        loadDatabaseStructure();
        // Initialize CodeMirror
        sqlEditor = wp.CodeMirror.fromTextArea(document.getElementById('wpsqlc-query-editor'), {
            mode: 'text/x-sql',
            theme: 'default',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            scrollbarStyle: 'native',
            extraKeys: {
                'Ctrl-Enter': executeQuery,
                'Cmd-Enter': executeQuery
            },
            placeholder: 'Enter your SQL query here...'
        });

        // Bind event handlers
        $('#wpsqlc-execute-btn').on('click', executeQuery);
        $('#wpsqlc-clear-btn').on('click', clearEditor);

        // Add keyboard shortcut hint
        $('.wpsqlc-editor-buttons').append(
            '<span class="wpsqlc-shortcut-hint">Tip: Press Ctrl+Enter to execute query</span>'
        );
    }

    // Execute SQL Query
    function executeQuery() {
        const query = sqlEditor.getValue().trim();

        if (!query) {
            showMessage('Please enter a SQL query', 'notice-warning');
            return;
        }

        // Disable execute button and show loading state
        $('#wpsqlc-execute-btn').prop('disabled', true).text('Executing...');
        showMessage('Executing query...', 'notice-info');

        // Send AJAX request
        $.ajax({
            url: wpsqlc.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsqlc_execute_query',
                nonce: wpsqlc.nonce,
                query: query
            },
            success: handleQueryResponse,
            error: handleQueryError,
            complete: function() {
                $('#wpsqlc-execute-btn').prop('disabled', false).text('Execute Query');
            }
        });
    }

    // Handle successful query response
    function handleQueryResponse(response) {
        if (!response.success) {
            showMessage('Error: ' + (response.data || 'Unknown error'), 'notice-error');
            return;
        }

        const results = response.data.result;

        if (!Array.isArray(results) || results.length === 0) {
            showMessage('Query executed successfully. No results to display.', 'notice-success');
            clearResultsTable();
            return;
        }

        // Build results table
        const table = buildResultsTable(results);
        $('#wpsqlc-results-message').hide();
        $('#wpsqlc-results-table').html(table);

        // Show success message with row count
        const rowCount = results.length;
        showMessage(
            `Query executed successfully. ${rowCount} row${rowCount !== 1 ? 's' : ''} returned.`,
            'notice-success'
        );
    }

    // Handle query error
    function handleQueryError(xhr, status, error) {
        showMessage('Error: ' + error, 'notice-error');
    }

    // Build results table HTML
    function buildResultsTable(results) {
        const table = $('<table></table>');
        
        // Create header
        const thead = $('<thead></thead>');
        const headerRow = $('<tr></tr>');
        Object.keys(results[0]).forEach(function(key) {
            headerRow.append($('<th></th>').text(key));
        });
        thead.append(headerRow);
        table.append(thead);

        // Create body
        const tbody = $('<tbody></tbody>');
        results.forEach(function(row) {
            const tr = $('<tr></tr>');
            Object.values(row).forEach(function(value) {
                tr.append($('<td></td>').text(value !== null ? value : 'NULL'));
            });
            tbody.append(tr);
        });
        table.append(tbody);

        return table;
    }

    // Show message in the results area
    function showMessage(message, type) {
        const messageEl = $('#wpsqlc-results-message');
        messageEl
            .removeClass('notice-error notice-success notice-info notice-warning')
            .addClass(type)
            .html('<p>' + message + '</p>')
            .show();
    }

    // Clear editor content
    function clearEditor() {
        sqlEditor.setValue('');
        clearResultsTable();
        showMessage('Execute a query to see results', 'notice-info');
    }

    // Clear results table
    function clearResultsTable() {
        $('#wpsqlc-results-table').empty();
    }

    // Load database structure
    function loadDatabaseStructure() {
        $.ajax({
            url: wpsqlc.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsqlc_get_db_structure',
                nonce: wpsqlc.nonce
            },
            success: function(response) {
                if (response.success) {
                    dbStructure = response.data;
                    renderTableList();
                } else {
                    showMessage('Error loading database structure: ' + response.data, 'notice-error');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error loading database structure: ' + error, 'notice-error');
            }
        });
    }

    // Render table list
    function renderTableList() {
        const container = $('#wpsqlc-table-list');
        container.empty();

        Object.keys(dbStructure).sort().forEach(function(tableName) {
            const tableInfo = dbStructure[tableName];
            const tableItem = $('<div></div>')
                .addClass('wpsqlc-table-item')
                .text(tableName)
                .on('click', function() {
                    $('.wpsqlc-table-item').removeClass('active');
                    $(this).addClass('active');
                    generateTableQuery(tableName, tableInfo);
                });
            container.append(tableItem);
        });
    }

    // Generate SQL query for selected table
    function generateTableQuery(tableName, tableInfo) {
        const query = `SELECT * FROM ${tableName} LIMIT 100;`;
        sqlEditor.setValue(query);
        sqlEditor.refresh();
    }

    // Initialize when document is ready
    $(document).ready(initSQLiteConsole);

})(jQuery);