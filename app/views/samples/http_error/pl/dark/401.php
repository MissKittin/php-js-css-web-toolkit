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
						<li>Kliknij przycisk <a href="javascript:location.reload()" target="_self"><img id="img_refresh" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAAQCAAAAADoyKrlAAABI2lDQ1BJQ0MgcHJvZmlsZQAAKJGdkLFKw1AUhr/EYkUUEcVBHDK4Flzs5FIVglAhxgpWpzRJsZjEkKQU38A30YfpIAg+gg+g4Ox/o4ODWbxw+D8O5/z/vRdsJwnTsrUHaVYVrt8bXg6vnPYbbdZpsYYdhGXe87w+jefzFcvoS8d4Nc/9eRajuAylc1UW5kUF1oG4O6tywyo2bwf+kfhB7ERpFomfxLtRGhk2u36aTMMfT3OblTi7ODd91Q4uJ5zi4TBiyoSEio40U+eYLvtSl4KAe0pCaUKs3kwzFTeiUk4uh6KBSLdpyNuu8zyljOQxkZdJuCOVp8nD/O/32sdZvWltzfOgCOrWgsoej+H9EVaHsPEMy9cNWUu/39Yw061n/vnGL4UYUDOeyiOjAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gcRBA8uCgsHCQAAAEhJREFUCNddjsENADEIw5wuHjF57lEB1fEiMjJR6BEwKYHDsnQyENLMSELRJRSEgMEG9rKAcdY9P7sO87z8O1vZzpqyW5ToTXxAdhsEjvmI3wAAAABJRU5ErkJggg==" alt="refresh.bmp (82 bajty)"></a> <a href="javascript:location.reload()" target="_self">Odśwież</a>.<br></li>
						<li>Jeśli adres strony został wpisany na pasku adresu, upewnij się, czy jest on wpisany poprawnie.<br></li>
						<li>Otwórz stronę główną <a href="/"><?php echo $_SERVER['HTTP_HOST']; ?></a> i poszukaj tam łączy do potrzebnych informacji.</li>
						<li>Kliknij przycisk <a href="javascript:history.back(1)"><img id="img_back" alt="back" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAQAQMAAAAYrwxEAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV/TSlUqInYQcchQnayIijhqFYpQIdQKrTqYXPoFTRqSFBdHwbXg4Mdi1cHFWVcHV0EQ/ABxdHJSdJES/5cWWsR4cNyPd/ced+8AoVZimhUYBzTdNpPxmJjOrIrBVwTRhwC6MCYzy5iTpAQ8x9c9fHy9i/Is73N/jh41azHAJxLPMsO0iTeIpzdtg/M+cZgVZJX4nHjUpAsSP3JdafAb57zLAs8Mm6nkPHGYWMy3sdLGrGBqxFPEEVXTKV9IN1jlvMVZK1VY8578haGsvrLMdZpDiGMRS5AgQkEFRZRgI0qrToqFJO3HPPyDrl8il0KuIhg5FlCGBtn1g//B726t3OREIykUAzpeHOdjGAjuAvWq43wfO079BPA/A1d6y1+uATOfpFdbWuQI6N0GLq5bmrIHXO4AA0+GbMqu5Kcp5HLA+xl9UwbovwW61xq9Nfdx+gCkqKvEDXBwCIzkKXvd492d7b39e6bZ3w8+dHKSmxKlXwAAAAZQTFRF////AAAAVcLTfgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB+YHEQQQOUSCjFAAAAAoSURBVAjXY/j/gQGCvn9geP6B4foHhg0MDPUPQAjIuA4W/A5T8/8DAILfGsPQASJYAAAAAElFTkSuQmCC"> Wstecz</a>, aby wypróbować inne łącze.</li>
					</ul>
					<p><br></p>
					<h2>HTTP 401 - Nieautoryzowany dostęp</h2>
				</td>
			</tr>
		</table>
	</body>
</html>