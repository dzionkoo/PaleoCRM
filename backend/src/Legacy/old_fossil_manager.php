<?php

/**
 * ⚠️ LEGACY CODE - PHP 5.6/7.4 
 * 
 * This is real-world legacy code you might encounter:
 * ❌ Uses deprecated mysql_* functions (removed in PHP 7.0)
 * ❌ Weak type hints and array manipulation
 * ❌ Global state and procedural code
 * ❌ No error handling or validation
 * ❌ SQL injection vulnerabilities
 * ❌ No documentation or comments
 * 
 * This file is HERE TO SHOW WHY MODERNIZATION MATTERS!
 */

// Old school database connection (DANGEROUS - DO NOT USE IN PRODUCTION)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'password123';
$db_name = 'paleocene_db';

$conn = mysql_connect($db_host, $db_user, $db_pass);
if (!$conn) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db($db_name, $conn);

/**
 * Get all fossils from database - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. Uses mysql_query (DEPRECATED since PHP 5.5, REMOVED in 7.0)
 * 2. No parameterized queries = SQL INJECTION VULNERABILITY
 * 3. Returns numerically indexed array, hard to understand
 * 4. No resource cleanup = potential memory leaks
 * 5. Magic strings and weak typing
 * 6. Error handling via die() = terrible for production
 */
function get_all_fossils($status = 'documented') {
    global $conn;
    
    // VULNERABLE: Direct string interpolation in SQL
    $query = "SELECT * FROM fossils WHERE status = '$status' ORDER BY estimated_age DESC";
    
    $result = mysql_query($query, $conn);
    
    if (!$result) {
        echo "Error in query: " . mysql_error();
        return array();
    }
    
    $fossils = array();
    while ($row = mysql_fetch_assoc($result)) {
        $fossils[] = $row;
    }
    
    mysql_free_result($result);
    return $fossils;
}

/**
 * Create new fossil - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. Weak validation - just isset checks
 * 2. Direct array access without type checking
 * 3. Variable assignment from unpredictable source
 * 4. No transaction handling
 * 5. Returns boolean, unclear what happened
 */
function create_fossil($data) {
    global $conn;
    
    if (!isset($data['species']) || !isset($data['weight'])) {
        return false;
    }
    
    $id = uniqid();
    $species = $data['species'];
    $weight = $data['weight'];
    $age = isset($data['age']) ? $data['age'] : 0;
    $location = isset($data['location']) ? $data['location'] : 'Unknown';
    
    // Multiple string interpolations - VERY VULNERABLE TO INJECTION
    $query = "INSERT INTO fossils (id, species, weight, estimated_age, discovery_location) 
              VALUES ('$id', '$species', $weight, $age, '$location')";
    
    $result = mysql_query($query, $conn);
    
    if ($result) {
        mysql_query("INSERT INTO audit_log (action, entity_id) VALUES ('CREATE_FOSSIL', '$id')");
        return true;
    }
    
    return false;
}

/**
 * Update fossil status - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. No type hints whatsoever
 * 2. Impossible to catch type errors early
 * 3. Direct object/array mutation in unclear context
 */
function update_fossil_status($id, $new_status) {
    global $conn;
    
    // Weak validation
    if (empty($id) || empty($new_status)) {
        return false;
    }
    
    $valid_statuses = array('active', 'extinct', 'documented');
    
    if (!in_array($new_status, $valid_statuses)) {
        return false;
    }
    
    // VULNERABLE: No prepared statements
    $query = "UPDATE fossils SET status = '$new_status' WHERE id = '$id'";
    
    $result = mysql_query($query, $conn);
    
    return $result !== false;
}

/**
 * Get fossil with statistics - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. Multiple sequential queries (N+1 problem)
 * 2. Manual array construction prone to errors
 * 3. Inconsistent return structure
 * 4. No pagination or limit handling
 */
function get_fossil_with_stats($id) {
    global $conn;
    
    $query = "SELECT * FROM fossils WHERE id = '$id'";
    $result = mysql_query($query);
    
    if (!$result || mysql_num_rows($result) === 0) {
        return null;
    }
    
    $fossil = mysql_fetch_assoc($result);
    
    // N+1 query problem - additional queries for every fossil
    $query2 = "SELECT COUNT(*) as views FROM audit_log WHERE entity_id = '$id' AND action = 'VIEW'";
    $result2 = mysql_query($query2);
    $stats = mysql_fetch_assoc($result2);
    
    $query3 = "SELECT COUNT(*) as edits FROM audit_log WHERE entity_id = '$id' AND action = 'UPDATE'";
    $result3 = mysql_query($query3);
    $edit_stats = mysql_fetch_assoc($result3);
    
    // Manual array building - easy to make mistakes
    return array(
        'id' => $fossil['id'],
        'species' => $fossil['species'],
        'age' => intval($fossil['estimated_age']),
        'weight' => floatval($fossil['weight']),
        'location' => $fossil['discovery_location'],
        'status' => $fossil['status'],
        'metrics' => array(
            'views' => intval($stats['views']),
            'edits' => intval($edit_stats['edits'])
        )
    );
}

/**
 * Delete fossil - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. Cascading deletes could be catastrophic without foreign keys
 * 2. No transaction safety
 * 3. No soft deletes
 * 4. Straight exec without confirmation
 */
function delete_fossil($id) {
    global $conn;
    
    // Could accidentally delete linked records!
    $query = "DELETE FROM fossils WHERE id = '$id'";
    $result = mysql_query($query);
    
    if ($result) {
        // Separate operation - could fail leaving orphaned records
        mysql_query("DELETE FROM audit_log WHERE entity_id = '$id'");
        return true;
    }
    
    return false;
}

/**
 * Export fossils to CSV - LEGACY IMPLEMENTATION
 * 
 * Problems:
 * 1. Direct string concatenation for CSV (no escaping)
 * 2. Memory intensive for large result sets
 * 3. No streaming support
 */
function export_fossils_csv($status) {
    $fossils = get_all_fossils($status);
    
    $csv = "ID,Species,Age,Weight,Location\n";
    
    foreach ($fossils as $fossil) {
        // No CSV escaping! Commas in data will break the file
        $csv .= "{$fossil['id']},{$fossil['species']},{$fossil['estimated_age']},{$fossil['weight']},{$fossil['discovery_location']}\n";
    }
    
    return $csv;
}

/**
 * ☠️ SECURITY ISSUES SUMMARY ☠️
 * 
 * 1. SQL Injection: Every query uses string interpolation
 * 2. Type Juggling: Weakly typed parameters allow unexpected inputs
 * 3. No Input Validation: Minimal validation of user input
 * 4. Global State: Dependency on global $conn makes testing impossible
 * 5. No Error Handling: die() and echo used for errors
 * 6. Performance: N+1 queries, no caching, no optimization
 * 7. Maintainability: No documentation, magic values, procedural spaghetti
 * 8. Testing: 100% untestable due to global dependencies
 * 
 * This code would FAIL any security audit, code review, or modern standards check!
 */
?>
