<?php
	// get the seed out of the url
	$seed = $_GET['seed'];

	// get images that should be completed if set, empty array if not
	$completed = isset($_POST['completed']) ? $_POST['completed'] : array();

	// get the contents in the icons folder
	$scan = scandir('./img/icons');
	// remove the current and parent directory
	$icons = array_diff($scan, ['.', '..']);

	// set the seed
	srand($seed);
	// shuffle the icons
	shuffle($icons);
?>

<table>
	<tbody>
		<tr>
			<?php foreach (array_slice($icons, 0, 5) as $icon): ?>
				<?php $bg = in_array($icon, $completed) ? 'bg-dark' : ''; ?>
				<td class="<?php echo $bg ?>"><img src="img/icons/<?php echo $icon  ?>"></td>
			<?php endforeach ?>
		</tr>
		<tr>
			<?php foreach (array_slice($icons, 5, 5) as $icon): ?>
				<?php $bg = in_array($icon, $completed) ? 'bg-dark' : ''; ?>
				<td class="<?php echo $bg ?>"><img src="img/icons/<?php echo $icon  ?>"></td>
			<?php endforeach ?>
		</tr>
		<tr>
			<?php $subset = array_slice($icons, 10, 4) ?>
			<?php for ($i = 0; $i < 5; $i++): ?>
				<?php if ($i != 2): ?>
					<?php 
						$icon = array_shift($subset);
						$bg = in_array($icon, $completed) ? 'bg-dark' : '';
					?>
					<td class="<?php echo $bg ?>">
						<img src="img/icons/<?php echo $icon ?>">
					</td>
				<?php else: ?>
					<td></td>
				<?php endif ?>
			<?php endfor; ?>
		</tr>
		<tr>
			<?php foreach (array_slice($icons, 14, 5) as $icon): ?>
				<?php $bg = in_array($icon, $completed) ? 'bg-dark' : ''; ?>
				<td class="<?php echo $bg ?>"><img src="img/icons/<?php echo $icon  ?>"></td>
			<?php endforeach ?>
		</tr>
		<tr>
			<?php foreach (array_slice($icons, 19, 5) as $icon): ?>
				<?php $bg = in_array($icon, $completed) ? 'bg-dark' : ''; ?>
				<td class="<?php echo $bg ?>"><img src="img/icons/<?php echo $icon  ?>"></td>
			<?php endforeach ?>
		</tr>
	</tbody>
</table>