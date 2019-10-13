<?php $info = $this->dashman->info() ?>

	</main>
	<footer class="py-3 mt-5 border-top">
		<div class="container">
			<div class="row">
				<div class="col-md-8">
					<span class="m-t-md m-r">server: <b><?php echo $info['server'] ?></b></span> &nbsp
					<span class="m-t-md m-r">platform: <b><?php echo $info['platform'] ?></b></span> &nbsp
					<span>version: <b><?php echo $info['version'] ?></b></span>
				</div>
				<div class="col-md-4 m-t-md text-right">Scafolda &copy; <?php echo date('Y') ?></div>
			</div>
		</div>
	</footer>
</div>

<script src="<?php echo base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?php echo base_url('assets/js/popper.min.js') ?>"></script>
<script src="<?php echo base_url('assets/js/bootstrap.min.js') ?>"></script>
<script src="<?php echo base_url('assets/js/app.js') ?>"></script>

<?php if (!empty($scripts)): ?>
	<?php foreach ($scripts as $script) echo $script ?>
<?php endif ?>

</body>
</html>