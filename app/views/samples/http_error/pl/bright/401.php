<!DOCTYPE html>
<html lang="pl-PL">
	<head>
		<title>HTTP 401 - Nieautoryzowany dostęp</title>
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
		<meta name="viewport" content="width=device-width,initial-scale=1">
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
					<h1>Nie można odnaleźć strony</h1>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Serwer odmówił dostępu do zasobu.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<hr>
					<p id="try_steps">Spróbuj wykonać następujące zadania:</p>
					<ul>
						<li>Kliknij przycisk <a href="javascript:location.reload()" target="_self"><img id="img_refresh" src="data:image/bmp;base64,Qk1GAQAAAAAAAEYAAAAoAAAADQAAABAAAAABAAgAAAAAAAABAAAAAAAAAAAAAAQAAAAEAAAA////AICAgAAAAAAAAAAAAAICAgICAgICAgICAgIANAACAAAAAAAAAAAAAAACAEEAAgAAAAAAAQAAAAAAAgAEAAIAAAAAAQEAAAAAAAIABAACAAAAAQEBAQEAAAACAAIAAgAAAAABAQAAAQAAAgDeAAIAAAAAAAEAAAEAAAIA4gACAAABAAAAAAABAAACAAYAAgAAAQAAAQAAAAAAAgGxAQIAAAEAAAEBAAAAAAIBWgECAAAAAQEBAQEAAAACAFoBAgAAAAAAAQEAAAAAAgCxAQIAAAAAAAEAAAICAgIBsQECAAAAAAAAAAACAAIAAVoBAgAAAAAAAAAAAgIAAABUAAICAgICAgICAgIAAAABDAE=" alt="refresh.bmp (82 bajty)"></a> <a href="javascript:location.reload()" target="_self">Odśwież</a>.<br></li>
						<li>Jeśli adres strony został wpisany na pasku adresu, upewnij się, czy jest on wpisany poprawnie.<br></li>
						<li>Otwórz stronę główną <a href="/"><?php echo $_SERVER['HTTP_HOST']; ?></a> i poszukaj tam łączy do potrzebnych informacji.</li>
						<li>Kliknij przycisk <a href="javascript:history.back(1)"><img id="img_back" alt="back" src="data:image/bmp;base64,Qk3+AAAAAAAAAD4AAAAoAAAADAAAABAAAAABAAgAAAAAAMAAAAAAAAAAAAAAAAIAAAACAAAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAABAQAAAAAAAAAAAAEAAQAAAAAAAAAAAQAAAQEBAQEBAQEBAAAAAAAAAAAAAAEBAAAAAAAAAAAAAAEAAQAAAQEBAQEBAQEAAAEAAQAAAAAAAAAAAAABAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA="> Wstecz</a>, aby wypróbować inne łącze.</li>
					</ul>
					<p><br></p>
					<h2>HTTP 401 - Nieautoryzowany dostęp</h2>
				</td>
			</tr>
		</table>
	</body>
</html>