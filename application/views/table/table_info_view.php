<?php $this->load->view('includes/header') ?>

<?php $this->load->view('table/table_header', array(
	'active' => 'info',
)); ?>

<p class="my-3"><b>Table details</b></p>

<table>
    <?php foreach($meta as $key => $value): ?>
        <tr>
            <td><?= $key ?></td>
            <th class="pl-3"><?= $value ?></th>
        </tr>
    <?php endforeach ?>
</table>

<?php $this->load->view('includes/footer') ?>