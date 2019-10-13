<?php $this->load->view('includes/header') ?>

<?php $this->load->view('table/table_header', array(
	'active' => 'foreign',
)); ?>

<?= form_open(current_url()) ?>
    <?php if(empty($meta)): ?>
        <p class="mt-4 text-muted">
            No foreign keys are registered.
        </p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Column</th>
                    <th>Referenced Schema</th>
                    <th>Referenced Table</th>
                    <th>Referenced Column</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <?php foreach($meta as $key => $refInfo): ?>
                <tr>
                    <th><?= $refInfo['COLUMN_NAME'] ?></th>
                    <td><?= $refInfo['REFERENCED_TABLE_SCHEMA'] ?></td>
                    <td><?= $refInfo['REFERENCED_TABLE_NAME'] ?></td>
                    <td><?= $refInfo['REFERENCED_COLUMN_NAME'] ?></td>
                    <td class="text-center">
                        <?= form_hidden('column', $refInfo['COLUMN_NAME']) ?>
                        <button type="submit" name="delete_key" value="<?= $refInfo['CONSTRAINT_NAME'] ?>" class="close float-none">
                            &times
                        </button>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>

<?= form_close() ?>

<?php $this->load->view('includes/footer') ?>