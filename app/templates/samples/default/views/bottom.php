		<script src="/assets/default.js"></script>
		<?php
			if(isset($view['scripts']))
				foreach($view['scripts'] as $script)
					{ ?><script src="<?php echo $script; ?>"></script><?php }
		?>
	</body>
</html>