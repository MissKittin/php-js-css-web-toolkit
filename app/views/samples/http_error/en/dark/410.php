<!DOCTYPE html>
<html lang="en">
	<head>
		<title>HTTP 410 Gone</title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Security-Policy" content="
			default-src 'none';
			script-src
				'unsafe-hashes'
				'sha256-U7+uFC/BDf1Ay0tFSNux2G6tD38fkySGcPsHeo8lALs='
				'sha256-BnjeEGnrzUpZQg/kHTKD3AKFpbHKs/7oEfQdHYQTtdM=';
			style-src
				'unsafe-hashes'
				'sha256-UtC9zeYeTufHnAkfGF/B3zD/oxSBVHJdQynQYHWPw+Y='

				'sha256-3Ioqe5khV/O4DKRrzCPxixEeT2YeqMmy66Rxwd1asBI=';
			img-src data:;
		">
		<meta name="robots" content="noindex,nofollow">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<style>
			body {
				color: #ffffff;
				background-color: #000000;
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
					<img alt="i" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAAhBAMAAADe7h2QAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV/TSlUqInYQcchQnayIijhqFYpQIdQKrTqYXPoFTRqSFBdHwbXg4Mdi1cHFWVcHV0EQ/ABxdHJSdJES/5cWWsR4cNyPd/ced+8AoVZimhUYBzTdNpPxmJjOrIrBVwTRhwC6MCYzy5iTpAQ8x9c9fHy9i/Is73N/jh41azHAJxLPMsO0iTeIpzdtg/M+cZgVZJX4nHjUpAsSP3JdafAb57zLAs8Mm6nkPHGYWMy3sdLGrGBqxFPEEVXTKV9IN1jlvMVZK1VY8578haGsvrLMdZpDiGMRS5AgQkEFRZRgI0qrToqFJO3HPPyDrl8il0KuIhg5FlCGBtn1g//B726t3OREIykUAzpeHOdjGAjuAvWq43wfO079BPA/A1d6y1+uATOfpFdbWuQI6N0GLq5bmrIHXO4AA0+GbMqu5Kcp5HLA+xl9UwbovwW61xq9Nfdx+gCkqKvEDXBwCIzkKXvd492d7b39e6bZ3w8+dHKSmxKlXwAAABhQTFRF////zMzMgICAAAD/AAAAAAAAAAAAAAAAYiAp1wAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB+YHEQQOGautk0cAAABeSURBVBjTY1CCARUXFwcGBQYoQOUpofAUVZB5CkIoPEYVJJ4Lij4GBkbCPEZlIwEEj8nYWAEXD1UlPjONjY0NkPSh8BiowgOaaYhiH0iSkVS/E8dTFEQAB1AQw4EDANwNHSfYT35NAAAAAElFTkSuQmCC">
				</td>
				<td id="td_header">
					<h1>The page does not exist</h1>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					The page you are looking for has been removed.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<hr>
					<p id="try_steps">You might find the information you need by opening the <a href="/"><?php echo $_SERVER['HTTP_HOST']; ?></a> home page and then linking to a similiar page.</p>
					<p><br></p>
					<h2>HTTP Error 410 - Permanently not available</h2>
				</td>
			</tr>
		</table>
	</body>
</html>