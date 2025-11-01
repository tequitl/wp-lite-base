<?php
/**
 * Comments template for ClassicMicroBlog (Vue-powered)
 * Uses Vue to render comments and the form; data fetched via REST API.
 */

if ( post_password_required() ) {
    echo '<p class="muted">This content is password protected. Enter the password to view comments.</p>';
    return;
}
?>

<div id="comments" class="comments-area">
    <div class="card">
        <div id="comments-app"></div>
        <noscript>
            <p class="muted">Comments require JavaScript. Please enable it to view and post comments.</p>
        </noscript>
    </div>
</div>