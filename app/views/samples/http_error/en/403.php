<!DOCTYPE html>
<html lang="pl">
	<head>
		<title>HTTP 403 - Forbidden</title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="
			default-src 'none';
			script-src
				'unsafe-hashes'
				'sha256-U7+uFC/BDf1Ay0tFSNux2G6tD38fkySGcPsHeo8lALs='
				'sha256-BnjeEGnrzUpZQg/kHTKD3AKFpbHKs/7oEfQdHYQTtdM=';
			style-src
				'unsafe-hashes'
				'sha256-aLRko+1AR2USGbyTf0MCTex+mhQWGkdlRVEvSg7088A='

				'sha256-hNgNhx1LkGHGLDUhsB3SwVcjG89/7dclDte6VbctThM=';
			img-src data:;
		">
		<meta name="robots" content="noindex,nofollow">
		<style>
			body {
				color: #000000;
				background-color: #ffffff;
				font: 8pt/11pt verdana;
			}
			a:link {
				color: #ff0000;
			}
			a:visited {
				color: #4e4e4e;
			}
			table {
				width: 400px;
				padding-left: 5px;
				border-spacing: 5px;
			}
			td {
				padding-bottom: 5px;
			}
			#td_img_info {
				vertical-align: bottom;
				text-align: left;
			}
			#td_header {
				vertical-align: middle;
				text-align: left;
				width: 360px;
			}
			#td_header h1 {
				padding-left: 5px;
			}
			h1 {
				font: 13pt/15pt verdana;
			}
			hr {
				background-color: #c0c0c0;
				border-width: 0;
				height: 2px;
				margin-bottom: 25px;
			}
			#try_steps {
				margin-bottom: 20px;
			}
			#img_refresh {
				vertical-align: middle;
			}
			#img_back {
				vertical-align: bottom;
			}
			h2 {
				font: 8pt/11pt verdana;
			}
		</style>
	</head>
	<body>
		<table>
			<tr>
				<td id="td_img_info">
					<img alt="i" src="data:image/bmp;base64,Qk3yAwAAAAAAAFYAAAAoAAAAGQAAACEAAAABAAgAAAAAAJwDAAAAAAAAAAAAAAgAAAAIAAAA////AMzMzACAgIAA/wAAAAAAAAAAAAAAAAAAAAAAAAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEAAYAAgEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBBABDAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQQA3QACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEEAOIAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAABBAFaAQIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQQABAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEEAAQAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAABBAFaAQIAAAAAAAAAAwMDAwMDAwMDAAAAAAAAAQQABAACAAAAAAAAAAABAwMDAwMBAAAAAAAAAAEEAVoBAgAAAAAAAAAAAAMDAwMDAAAAAAAAAAABBIAuggIAAAAAAAAAAAADAwMDAwAAAAAAAAAAAQSALoICAAAAAAAAAAAAAwMDAwMAAAAAAAAAAAEE1N0AAgAAAAAAAAAAAAMDAwMDAAAAAAAAAAABBHdxdwIAAAAAAAAAAAADAwMDAwAAAAAAAAAAAQR3d3cCAAAAAAAAAAABAwMDAwMAAAAAAAAAAAEE3d3QAgAAAAAAAAADAwMDAwMDAAAAAAAAAAABBN3d3QIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQQHtwcCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEE3d3dAgAAAAAAAAAAAQIDAwIBAAAAAAAAAAABBAAN0AIAAAAAAAAAAAIDAwMDAgAAAAAAAAAAAQTd3dQCAAAAAAAAAAACAwMDAwIAAAAAAAAAAAEEd3d3AgAAAAAAAAAAAQIDAwIBAAAAAAAAAAABBITQDQIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQR3cXcCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEEcNDXAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAABBETd0AIAAAAAAAAAAAAAAAAAAAAAAAQEBAQEBAR3d3cCAAAAAAAAAAAAAAAAAAAAAAACAAABAgQAR3F3AgAAAAAAAAAAAAAAAAAAAAAAAgABAgQAALe3twIAAAAAAAAAAAAAAAAAAAAAAAIBAgQAAABN3dACAAAAAAAAAAAAAAAAAAAAAAACAgQAAAAARN1NAgAAAAAAAAAAAAAAAAAAAAAAAgQAAAAAAAAAAAICAgICAgICAgICAgICAgICAgIAAAAAAADYTQA=">
				</td>
				<td id="td_header">
					<h1>The page cannot be found</h1>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					The server has refused access to the resource.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<hr>
					<p id="try_steps">Please try the following:</p>
					<ul>
						<li>If you typed the page address in the Address bar, make sure that it is spelled correctly.<br></li>
						<li>Open the <a href="/"><?php echo $_SERVER['HTTP_HOST']; ?></a> home page, and then look for links to the information you want.</li>
						<li>Click the <a href="javascript:history.back(1)"><img id="img_back" alt="back" src="data:image/bmp;base64,Qk3+AAAAAAAAAD4AAAAoAAAADAAAABAAAAABAAgAAAAAAMAAAAAAAAAAAAAAAAIAAAACAAAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAABAQAAAAAAAAAAAAEAAQAAAAAAAAAAAQAAAQEBAQEBAQEBAAAAAAAAAAAAAAEBAAAAAAAAAAAAAAEAAQAAAQEBAQEBAQEAAAEAAQAAAAAAAAAAAAABAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA="> Back</a> button to try another link.</li>
					</ul>
					<p><br></p>
					<h2>HTTP 403 - Forbidden</h2>
				</td>
			</tr>
		</table>
	</body>
</html>