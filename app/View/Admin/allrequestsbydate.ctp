<?php
  $this->Html->script('admin', array('inline' => false)); //this adds js to this page put these files in /app/webroot/js
  $this->Html->script('datepicker', array('inline' => false)); //this adds js to this page put these files in /app/webroot/js
  $this->Html->script('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js', array('inline' => false));
  $this->Html->css('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array('inline' => false));
  echo $this->Element('admin-navigation'); 
?>
		<div class="row">
			<div class="col-sm-8">
				<h3>All Requests by Date</h3>
			</div>
		</div>

		<div class="row">
		  <div class="col-sm-12">
  		  <?php 
          echo $this->Form->create('Admin', array('novalidate' => true));

          echo $this->Form->input('min_date',
                                  array('before' => '<p class="lead">Please choose a date range</p>',
                                        'type' => 'text',
                                        'label' => 'Start', 
                                        'class' => 'form-control date-picker autocomplete'));
          echo $this->Form->input('max_date',
                                  array('type' => 'text',
                                        'label' => 'End', 
                                        'class' => 'form-control date-picker autocomplete'));

          echo $this->Form->submit(
              'Create Report', 
              array('class' => 'btn btn-primary', 'title' => 'Create Report')
          );
          echo $this->Form->end();
        ?>
		  </div>
		</div>
	</div><!--END FROM ADMIN NAV -->
</div>
<div class="clearfix"></div>
