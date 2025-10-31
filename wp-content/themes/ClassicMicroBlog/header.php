<?php
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<title><?php bloginfo('name'); ?> - ClassicMicroBlog</title>
</head>
<body <?php body_class(); ?>>
<header class="header">
  ClassicMicroBlog
  <!-- Simple search box to resemble X header -->
  <input class="search" type="search" placeholder="Search">
</header>
