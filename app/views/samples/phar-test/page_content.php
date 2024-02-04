<h1>Phar <?php if(!$view['phar_included']) echo 'not '; ?>included</h1>
<h1>Component admin_panel <?php if(!$view['admin_panel_loaded']) echo 'not '; ?>loaded</h1>
<h1>Component middleware_form <?php if(!$view['middleware_form_loaded']) echo 'not '; ?>loaded</h1>
<hr>

<h2>Included files:</h2>
<pre><?php echo $view['included_files']; ?></pre>

<h2>Declared classes:</h2>
<pre><?php echo $view['declared_classes']; ?></pre>

<h2>Defined functions:</h2>
<pre><?php echo $view['defined_functions']; ?></pre>