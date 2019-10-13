<?php $this->load->view('includes/header') ?>

<?php $this->load->view('table/table_header', array(
	'active' => 'indexes',
)); ?>

<?php if(empty($meta)): ?>
    <p class="mt-4 text-muted">
        No Indexes are registered.
    </p>
<?php else: ?>
    <?= form_open(current_url()) ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Column</th>
                    <th>Key</th>
                    <th>Type</th>
                    <th>Unique</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <?php foreach($meta as $key => $refInfo): ?>
            <tr>
                <th>
                    <?= $refInfo['Column_name'] ?>
                </th>
                <td>
                    <?= $refInfo['Key_name'] ?>
                </td>
                <td>
                    <?= $refInfo['Index_type'] ?>
                </td>
                <td>
                    <?= ($refInfo['Non_unique'] == 0) ? 'TRUE' : 'FALSE' ?>
                </td>
                <td class="text-center">
                    <button type="submit" name="delete_key" value="<?= $refInfo['Key_name'] ?>" class="close float-none">
                        &times
                    </button>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    <?= form_close() ?>
<?php endif ?>

<?php $this->load->view('includes/footer') ?>