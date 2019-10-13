<div class="modal fade" id="deltbl-modal">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					Delete table <code><?php echo $table ?></code>
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				All data will be lost and can not be recovered.
				<div class="text-right">
					<a class="btn text-danger" href="<?php echo site_url('delete-table/'.$table) ?>">
						Delete
					</a>
				</div>
			</div>
		</div>
	</div>
</div>