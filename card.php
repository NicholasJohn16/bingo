<?php

	// get the seed out of the url
	$seed = $_GET['seed'];

	//if the seed doesn't exist
	if (!$seed) {
		//get the current url
		$url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		// then create a seed and append it to the url and reidrect to that url
		header('Location: '.$url.'?seed='.rand());
		// end processing
		exit();
	}

	// get the contents in the icons folder
	$scan = scandir('./img/icons');
	// remove the current and parent directory
	$icons = array_diff($scan, ['.', '..']);

	// set the seed
	srand($seed);
	// shuffle the icons
	shuffle($icons);
?>

<html>
	<head>
		<title>Bingo</title>
		<style>
			body {
				/* background-image: url('img/background.png'); */
				/*background-attachment: fixed ;*/
				background-repeat: no-repeat;
				/*background-size: cover;*/
				background-position: center;
				/*overflow:hidden;*/
				margin: 0;
			}
			table {
				/*margin: auto;*/
				/* margin-top: 104px;
				margin-left: 50px; */
				/*background-attachment: fixed;
				background-repeat: no-repeat;
				background-size: cover;
				background-position: center center;*/
				/*overflow:hidden;*/
				position: relative;
			}
			table:after {
				content: "";
				position: absolute;
				top: -200px;
				left: -200px;
				width: calc(100% + 400px);
				height: calc(100% + 400px);
				z-index: -1;

				background-image: url('img/bingo_card.png');
				background-position: center center;
				/*background-size: cover;*/
				background-repeat: no-repeat;
			}
			tbody td {
				height: 120px;
				width: 120px;
				min-height: 120px;
				min-width: 120px;

				background-position: center bottom;
				background-size: contain;
				background-repeat: no-repeat;
				padding: 0;
			}
			tbody td img {
				width: 100%;
				height: auto;
				pointer-events: none;

				-webkit-user-select: none;
				-khtml-user-select: none;
				-moz-user-select: none;
				-o-user-select: none;
				user-select: none;
			}
			.highlight {
				background-color: black;
			}
			.hightlight:empty {
				background-color: rgba(0, 0, 0, .5);
			}
			.highlight img {
				opacity: .5;
			}
			.seed {
				position: absolute;
				bottom: 0;
				right: 0;
				padding: .5rem;
				align-self: flex-end;
			}
			.flex {
				display: flex;
				align-items: center;
				justify-content: center;
				height: 100vh;
			}
		</style>
		<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>

		<script>
			$(document).ready(function() {

				$('td').on('click', function() {
					$(this).toggleClass('highlight');
				});

			});
		</script>
	</head>
	<body>
		<div class="flex">
			
			<?php include 'generator.php' ?>
			
		</div>

	</body>
</html>
