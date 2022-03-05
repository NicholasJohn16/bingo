<?php
	$post = $_POST;

	$files = glob('*.json');

	$defaults = array(
		'questions-seed' => rand(),
		'participants' => array(
			array('name' => 'First Player', 'seed' => rand())
		),
		'winners' => array(),
		'clues' => array(),
		'json' => '',
		'completed' => array()
	);

	$data = array_replace_recursive($defaults, $_POST);
	
	if(count($files) === 1) {
		$data['json'] = $files[0];
	}

	if($data['json']) {
		$json = file_get_contents($data['json']);
		$clues = json_decode($json, true);

		srand($data['questions-seed']);
		shuffle($clues);

		$data['clues'] = array_replace_recursive($data['clues'], $clues);
	}

?>

<html>
	<head>
		<title>Bingo Administration</title>
		<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.1/css/bootstrap.min.css" integrity="sha384-VCmXjywReHh4PwowAiWNagnWcLhlEJLA5buUprzK8rxFgeH0kww/aWY76TfkUoSX" crossorigin="anonymous"> -->
		<link href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cyborg/bootstrap.min.css" rel="stylesheet" integrity="sha384-mtS696VnV9qeIoC8w/PrPoRzJ5gwydRVn0oQ9b+RJOPxE1Z1jXuuJcyeNxvNZhdx" crossorigin="anonymous">
		<script
			src="https://code.jquery.com/jquery-3.4.1.min.js"
			integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
			crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>

		<style>
			textarea {
				overflow: hidden;
			}
			textarea, input, select {
				background-color: #222 !important;
				color: #fff !important;
			}
			div > table {
				table-layout: fixed;
				width: 100%;
			}
			td > img {
				display: block;
				width: 100%;
				height: auto;
			}
			.fa, .fab, .fal, .far, .fas {
				line-height: inherit !important;
			}

			/*.copy-dropdown.dropdown-toggle:after { content: none }*/
			.participants .btn.copy-dropdown {
				border-top-left-radius: 0;
				border-bottom-left-radius: 0;
				padding-left: .5rem;
				padding-right: .5rem;
			}
			.participants .btn {
				padding: 0.375rem .85rem;
			}
		</style>

		<script>
			const texts = {
				'verifying': '{player} has called a bingo!  Standby while we check their card...',
				'confirmed': 'The bingo has been confirmed! {player} is now out for the remainder of this round.',
				'refuted': 'The bingo that {player} called has not been verified. Bingo will resume momentarily...'
			};


			$(document).ready(function() {

				$('body').tooltip({
					selector: '[data-toggle="tooltip"]'
				});

				function updateCardPreviews() {
					var form = $('form');
					var formRows = $('.participants').find('.form-row');

					formRows.each(function(index, formRow) {
						var seed = $(formRow).find('input[name*=seed]').val();

						$.ajax({
							url: 'generator.php?seed='+seed,
							method: 'POST',
							data: form.serializeArray()
						}).then(function(data) {
							$(formRow).find('.bingo-card-preview').html(data);
						});
					});
				}

				updateCardPreviews();

				$('form').on('change', function() {
					updateCardPreviews();
				});

				function updateRemoveIcon() {
					var formRows = $('.participants').find('.form-row');

					if(formRows.length > 1) {
						formRows.find('.remove-participant').removeClass('d-none');
						formRows.find('.mark-winner').removeClass('d-none');
					} else {
						formRows.find('.remove-participant').addClass('d-none');
						formRows.find('.mark-winner').addClass('d-none');
					}
				}

				$('.add-participant').on('click', function(evt) {
					evt.preventDefault();

					createParticipant();
					updateRemoveIcon();
					updateCardPreviews();
				});

				function createParticipant(name, seed) {
					var max = 2147483647;
					var seed = seed ? seed : Math.floor(Math.random() * (+max - 0)) + 0;

					var rows = $('.participants .form-row');
					var template = $('#participant-template').html();
					template = $(template).clone();

					var seedInput = template.find('input[name=seed]');
					seedInput.val(seed);
					seedInput.attr('name', 'participants[' + rows.length + '][seed]');

					var nameInput = template.find('input[name=name]');
					if(name) { nameInput.val(name); }
					nameInput.attr('name', 'participants[' + rows.length + '][name]');

					template.find('.bingo-card-preview').attr('id', 'card-'+rows.length);

					template.find('button[name=check-seed]').attr('name', 'participants[' + rows.length + ']["check-seed"]');

					$('.participants').append(template);
				}

				$('.participants').on('click', '.mark-winner', function(evt) {
					evt.preventDefault();
					$(this).tooltip('hide');
					var formRow = $(this).parents('.form-row');
					var name = getPlayerName(formRow);
					var seed = formRow.find('input[name*=seed]').val();


					var result = confirm('Are you sure you want to mark ' + name + ' as winner?');

					if(!result) {
						return false;
					}

					var rows = $('.winners .form-row');
					var template = $('#winner-template').html();
					template = $(template).clone();

					var nameInput = template.find('input[name=name]');
					nameInput.val(name);
					nameInput.attr('name', 'winners[' + rows.length + '][name]');

					var tierInput = template.find('input[name=tier]');
					tierInput.attr('name', 'winners['+ rows.length + '][tier]');

					var seedInput = template.find('input[name=seed]');
					seedInput.val(seed)
					seedInput.attr('name', 'winners[' + rows.length + '][seed]');

					$('.winners').append(template);
					formRow.remove();
					updateRemoveIcon();
				});

				$('.participants').on('click', '.remove-participant', function(evt) {
					evt.preventDefault();
					var formRow = $(this).parents('.form-row');

					var name = formRow.find('input[name*=name]').val();
					name = name ? name : 'participant';

					var result = confirm('Are you sure you want to remove ' + name + '?');

					if(result) {
						formRow.find('[data-toggle="tooltip"]').tooltip('hide');
						formRow.remove();

						updateRemoveIcon();
					}
				});

				$('.winners').on('click', '.remove-winner', function(evt) {
					evt.preventDefault();

					var formRow = $(this).parents('.form-row');
					var name = formRow.find('input[name*=name]').val();
					var seed = formRow.find('input[name*=seed]').val();

					createParticipant(name, seed);
					updateRemoveIcon();
					updateCardPreviews();
					formRow.remove();
				});

				$('.participants').on('click', '.toggle-preview', function(evt) {
					evt.preventDefault();

					var togglePreview = $(this).parents('.form-row').find('.bingo-card-preview');

					togglePreview.collapse('toggle');

				});

				// Returns a function, that, as long as it continues to be invoked, will not
				// be triggered. The function will be called after it stops being called for
				// N milliseconds. If `immediate` is passed, trigger the function on the
				// leading edge, instead of the trailing.
				function debounce(func, wait, immediate) {
					var timeout;
					return function() {
						var context = this, args = arguments;
						var later = function() {
							timeout = null;
							if (!immediate) func.apply(context, args);
						};
						var callNow = immediate && !timeout;
						clearTimeout(timeout);
						timeout = setTimeout(later, wait);
						if (callNow) func.apply(context, args);
					};
				};

				var debouncedSearch = debounce(function(evt) {
					var search = $(evt.target).val().toLowerCase();

					var inputs = $(".participants .form-row input[name*='name']");

					inputs.each(function(index, input) {
						var input = $(input);
						var formRow = input.parents('.form-row');

						if(!input.val().toLowerCase().includes(search)) {
							formRow.addClass('d-none');
						} else {
							formRow.removeClass('d-none');
						}
					});
				}, 250);

				$('.search-participants').on('keyup', debouncedSearch);

				$('.clear-participants-search').on('click', function(evt) {
					$('.search-participants').val('');
					$('.participants .form-row').removeClass('d-none');
				});

				function getLink(formRow) {
					var seed = formRow.find('input[name*=seed]').val();
					var path = window.location.hostname == 'localhost' ? '/playground/bingo-card/' : '/stonewall/bingo/pride/';
					return window.location.protocol + '//' +window.location.hostname + path + "card.php?seed=" + seed;
				}

				new ClipboardJS('.copy-link', {
					text: function(trigger) {
						var formRow = $(trigger).parents('.form-row');

						return getLink(formRow);
					}
				});

				new ClipboardJS('.copy-tell', {
					text: function(trigger) {
						var formRow = $(trigger).parents('.form-row');
						var text = '/tell "';

						text += getPlayerName(formRow);
						text += '" Your bingo card link is ';
						text += getLink(formRow);

						return text;
					}
				});

				new ClipboardJS('.copy-clue');

				new ClipboardJS('.copy-text', {
					text: function(trigger) {
						var formRow = $(trigger).parents('.form-row');
						var name = getPlayerName(formRow);
						var key = $(trigger).data('key');
						
						return texts[key].replace('{player}', name);
					}
				});

				function getPlayerName(row) {
					return row.find('input[name*=name]').val();
				}

				$('.participants').on('click', '.copy-link, .copy-tell', function(evt) {
					evt.preventDefault();
					$('.tooltip-inner').html('Copied!');
				});

				$('.clues').on('click', '.copy-clue', function(evt) {
					evt.preventDefault();
					$('.tooltip-inner').html('Copied!');
				});

				$(window).on('beforeunload', function() {
					return 'Any changes will be lost';
				});

				$('form').on('submit', function() {
					$(window).off('beforeunload');
				});


			});

		</script>
	</head>
	<body>


		<div class="container">
			<div class="row d-none">
				<div class="col-6">
					<pre>
						<?php //print_r($post) ?>
					</pre>
				</div>
			</div>


			<h1 class="display-4 mb-5">Bingo Administration</h1>
			
			<form method="post">

				<div class="form-row mb-3">
					<div class="form-inline">
						<label class="my-1 mr-2" for="questions-seed">Questions Seed</label>
						<input type="text" class="form-control" name="questions-seed" value="<?php echo $data['questions-seed'] ?>">

						<label for="json-select" class="my-1 mx-2">JSON File</label>
						<select class="form-control" name="json" id="json-select">
							<option value="">Select a JSON file to load</option>
							<?php foreach ($files as $index => $file): ?>
								<?php $selected = $file == $data['json'] ? 'selected' : '' ?>
								<option value="<?php echo $file ?>" <?php echo $selected ?>><?php echo $file ?></option>
							<?php endforeach ?>
						</select>

						<button 
							class="btn btn-primary mx-2" 
							style="height:35px" 
							type="submit" 
							data-toggle="tooltip" 
							name="set-question-seed"
							value="true"
							title="Set Question Seed"><i class="fas fa-play"></i></button>
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						<h5 class="mb-3">Clues</h5>

						<div class="Clues">

							<?php foreach ($data['clues'] as $index => $clue): ?>
								<div class="row">
									<div class="col">
										<textarea name="" id="clue-<?php echo $index ?>" cols="30" rows="2" class="form-control"><?php echo $clue['text'] ?></textarea>
									</div>
								</div>

								<div class="row mt-1 mb-2">
									<div class="col">
										<div class="custom-control custom-checkbox float-left my-1">
											<?php $checked = in_array($clue['icon'], $data['completed']) ? 'checked' : '' ?>
											<input type="checkbox" id="clue-<?php echo $index ?>-checkbox" name="completed[<?php echo $index ?>]" value="<?php echo $clue['icon'] ?>" class="custom-control-input" <?php echo $checked ?>>
											<label class="custom-control-label" for="clue-<?php echo $index ?>-checkbox">Question Sent</label>
										</div>
										
										<button 
											class="btn btn-primary float-right copy-clue"
											data-clipboard-target="#clue-<?php echo $index ?>"
											data-toggle="tooltip"
											title="Copy Text"
											><i class="far fa-copy"></i></button>
									</div>
								</div>
							<?php endforeach ?>
						</div>
					</div>

					<div class="col-6">
						<h5 class="mb-3">Participants</h5>

						<div class="form-inline mb-3">
							<div class="form-group">
								<input type="search" class="form-control search-participants" placeholder="Filter participants">
							</div>
							<div class="form-group ml-2">
								<button class="btn btn-outline-danger clear-participants-search" type="button"><i class="fas fa-times"></i></button>
							</div>
						</div>

						<script id="participant-template" type="text/x-custom-template">
							<div class="form-row mb-2">
								<div class="col">
									<input type="text" class="form-control" name="name" value="" placeholder="Name">
								</div>
								<div class="col">
									<input type="text" class="form-control" name="seed" value="" placeholder="Seed">
								</div>
								<div class="col-6">
									<div class="btn-group">
										<button class="btn btn-info copy-link" data-toggle="tooltip" title="Copy Link"><i class="fas fa-link"></i></button>
										<button class="btn btn-info copy-tell" data-toggle="tooltip" title="Copy Tell"><i class="far fa-comment"></i></button>
										<button class="btn btn-info dropdown-toggle dropdown-toggle-split copy-dropdown" type="button" data-toggle="dropdown"></button>
										<div class="dropdown-menu">
											<a href="#" class="dropdown-item"><i class="fas fa-check"></i> Checking</a>
											<a href="#" class="dropdown-item"><i class="fas fa-check-double"></i> Verified</a>
											<a href="#" class="dropdown-item"><i class="fas fa-times"></i> Not Verified</a>
										</div>
									</div>
									<button class="btn btn-light toggle-preview" data-toggle="tooltip" title="Toggle Card"><i class="fas fa-th"></i></button>
									<button class="btn btn-danger float-right remove-participant" data-toggle="tooltip" title="Remove Participant"><i class="fas fa-times"></i></button>
									<a class="btn btn-warning text-white mark-winner float-right mr-1" title="Mark Winner" data-toggle="tooltip"><i class="fas fa-trophy"></i></a>
								</div>
								<div class="col-6 offset-3 collapse bingo-card-preview mt-2"></div>
							</div>
						</script>

						<div class="participants mb-3">
							<?php foreach ($data['participants'] as $index => $participant): ?>
								<div class="form-row mb-2">
									<div class="col">
										<input type="text" class="form-control" name="participants[<?php echo $index ?>][name]" value="<?= $participant['name'] ?>" placeholder="Name">
									</div>
									<div class="col">
										<input type="text" class="form-control" name="participants[<?php echo $index ?>][seed]" value="<?= $participant['seed'] ?>" placeholder="Seed">
									</div>
									<div class="col-6">
										<div class="btn-group">
											<button class="btn btn-info copy-link" data-toggle="tooltip" title="Copy Link"><i class="fas fa-link"></i></button>
											<button class="btn btn-info copy-tell" data-toggle="tooltip" title="Copy Tell"><i class="far fa-comment"></i></button>
											<button class="btn btn-info dropdown-toggle dropdown-toggle-split copy-dropdown" type="button" data-toggle="dropdown"></button>
											<div class="dropdown-menu">
												<a href="#" class="dropdown-item copy-text" data-key="verifying"><i class="fas fa-check"></i> Verifying</a>
												<a href="#" class="dropdown-item copy-text" data-key="confirmed"><i class="fas fa-check-double"></i> Confimed</a>
												<a href="#" class="dropdown-item copy-text" data-key="refuted"><i class="fas fa-times"></i> Refuted</a>
											</div>
										</div>
										<button class="btn btn-light toggle-preview" data-toggle="tooltip" title="Toggle Card"><i class="fas fa-th"></i></i></button>
										<?php $display = count($data['participants']) > 1 ? '' : 'd-none' ?>
										<button class="btn btn-danger float-right remove-participant <?php echo $display ?>" data-toggle="tooltip" title="Remove Participant"><i class="fas fa-times"></i></button>
										<a class="btn btn-warning text-white float-right mark-winner mr-1 <?php echo $display ?>" title="Mark Winner" data-toggle="tooltip"><i class="fas fa-trophy"></i></a>
									</div>
									<div class="col-6 offset-3 collapse bingo-card-preview mt-2"></div>
								</div>
							<?php endforeach ?>
						</div>

						<div class="btn-toolbar mb-3 justify-content-end">
							<button class="btn btn-success font-weight-bold float-right add-participant">Add Participant</button>
						</div>

						<script id="winner-template" type="text/x-custom-template">
							<div class="form-row mb-2">
								<div class="col">
									<input type="text" class="form-control" name="name" placeholder="Name">
								</div>
								<div class="col">
									<select class="custom-select" name="tier">
										<option value="row">Row</option>
										<option value="column">Column</option>
										<option value="cross">Cross</option>
										<option value="xmark">X Mark</option>
										<option value="blackout">Blackout</option>
									</select>
								</div>
								<div class="col-1">
									<button class="btn btn-danger remove-winner"><i class="fas fa-times"></i></button>
								</div>
								<input type="hidden" name="seed">
							</div>
						</script>

						<div class="winners mb-3">
							<h5 class="mb-3">Winners</h5>

							<?php $options = ['row' => 'Row', 'column' => 'Column', 'cross' => 'Cross', 'xmark' => 'X Mark', 'blackout' => 'Blackout']; ?>
							<?php foreach ($data['winners'] as $index => $winner): ?>
								<div class="form-row mb-2">
									<div class="col">
										<input type="text" class="form-control" name="winners[<?= $index ?>][name]" value="<?= $winner['name'] ?>" placeholder="Name">
									</div>
									<div class="col">
										<select class="custom-select" name="winners[<?= $index ?>][tier]">
											<?php foreach ($options as $value => $text): ?>
												<?php $selected = $winner['tier'] == $value ? 'selected' : ''; ?>
												<option value="<?= $value ?>" <?= $selected ?> ><?= $text ?></option>
											<?php endforeach ?>
										</select>
									</div>
									<div class="col-1">
										<button class="btn btn-danger remove-winner"><i class="fas fa-times"></i></button>
									</div>
									<input type="hidden" name="winners[<?= $index ?>][seed]" value="<?= $winner['seed'] ?>">
								</div>
							<?php endforeach ?>
						</div>

					</div>
				</div>


			</form>
		</div>

	</body>
</html>