<!DOCTYPE html>
<html>
	<head>
		<title>Herring report <?php if(!$this->_no_view_date) echo date('Y-m-d H:i'); ?></title>
		<meta charset="utf-8">
		<style>
			body {
				color: #000000;
				background-color: #dddddd;
			}
			h1, h2 {
				text-align: center;
			}
			table {
				margin-left: auto;
				margin-right: auto;
				margin-bottom: 20px;
				text-align: center;
			}
			table, tr, th, td {
				border: 1px solid #555555;
				border-collapse: collapse;
			}
			tr:nth-child(even) {
				background-color: #eeeeee;
			}
			.tr_highlight {
				color: #ffffff;
				background-color: #000000 !important;
			}
			th {
				color: #ffffff;
				background-color: #555555;
			}
			td {
				padding: 3px;
				white-space: nowrap;
			}
			.grid_table, .grid_tr, .grid_td {
				border: none;
				margin-bottom: 0;
			}
		</style>
		<script>
			document.addEventListener('DOMContentLoaded', function(){
				<?php $this->load_library(['sortTable.js'=>null]); ?>

				var i;

				var tableRows=document.getElementsByTagName('td');
				for(i=0; i<tableRows.length; i++)
				{
					tableRows[i].addEventListener('mouseover', function(){
						if(!this.parentElement.parentElement.parentElement.classList.contains('grid_table'))
							this.parentElement.classList.add('tr_highlight');
					});

					tableRows[i].addEventListener('mouseleave', function(){
						if(!this.parentElement.parentElement.parentElement.classList.contains('grid_table'))
							this.parentElement.classList.remove('tr_highlight');
					});
				}

				var tables=document.getElementsByTagName('table');
				for(i=0; i<tables.length; i++)
					if(!tables[i].classList.contains('grid_table'))
						sortTable(tables[i]);
			}, false);
		</script>
	</head>
	<body>
		<h1>Herring report</h1>
		<h2><?php if(!$this->_no_view_date) echo date('Y-m-d H:i'); ?></h2>