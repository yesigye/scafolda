<div class="text-right my-2 mb-md-0">
	<button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#modal-confirmDelete">
		Delete Record
	</button>
</div>

<nav class="nav nav-tabs mb-4">
	<a  href="<?= site_url("$table/$position") ?>" class="nav-link <?= ($ref_table == null) ? 'active' : null ?>">Details</a>
	<?php foreach($direct_references as $link): ?>
	<a href="<?= site_url("$table/$position/$link") ?>" class="nav-link <?= ($ref_table == $link) ? 'active' : null ?>" href="#">
		<?php echo $link ?>
	</a>
	<?php endforeach ?>
</nav>

<?php echo form_open(current_url()) ?>
<div class="modal" tabindex="-1" role="dialog" id="modal-confirmDelete">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this record and all related data?
                    <br>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="submit" name="delete" value="delete" class="btn btn-danger">Yes, Delete</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?php echo form_close() ?>