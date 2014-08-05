<?php 
  $this->Html->script('datepicker', array('inline' => false)); //this adds js to this page put these files in /app/webroot/js
  $this->Html->script('requests', array('inline' => false)); //this adds js to this page put these files in /app/webroot/js
  $this->Html->script('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js', array('inline' => false));
  $this->Html->css('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array('inline' => false));
?>
<div class="row">
  <div class="col-sm-8">
    <h1>Explore <span id="request-count" class="badge badge-info badge-lg"><?php echo $total; ?></span> requests and counting</h1>
  </div>
  <div class="col-sm-4">
    <p><?php echo $this->Html->link('Request New Record', array('action' => 'create'),array('class' => 'btn btn-lg btn-primary')); ?> </p>
  </div>
</div>
<div class="row">
  <div class="col-sm-10">
    <p class="intro-text">RecordTrac makes every public records request available to the public, including messages or documents uploaded by agency staff. Search through current and past requests. You may find what you need!</p>
  </div>
</div>
<div class="row">
	<div class="col-sm-3">
	  <div class="well">
	  <?php
      echo $this->Form->create('Request', array('action' => 'index', 'type' => 'get', 'novalidate' => true));
      echo $this->Form->input('term',
                              array('type' => 'text', 
                                    'placeholder' => 'I\'d like to find...',
                                    'label' => 'Search', 
                                    'class' => 'form-control'));   
      echo "<h5 class=\"underline\">ADVANCED FILTER</h5>";
      if ($this->Session->read('Auth.User')){
        echo $this->Form->input('my_filter',
                              array('label' => 'My Requests',
                                    'multiple' => 'checkbox',
                                    'class' => 'checkbox autocomplete',
                                    'options' => array('as Point of Contact', 'as Helper')));
      }                  
      echo $this->Form->input('status',
                              array('type' => 'select',
                                    'multiple' => 'checkbox', 
                                    'label' => 'Status',
                                    'class' => 'checkbox autocomplete'));
      echo $this->Form->input('min_date',
                              array('before' => '<p class="lead">Date Received</p>',
                                    'type' => 'text',
                                    'label' => 'Start', 
                                    'class' => 'form-control date-picker autocomplete'));
      echo $this->Form->input('max_date',
                              array('type' => 'text',
                                    'label' => 'End', 
                                    'class' => 'form-control date-picker autocomplete'));
      if ($this->Session->read('Auth.User')){
        echo $this->Form->input('requester',
                              array('label' => 'Requester Name',
                                    'class' => 'form-control',
                                    'placeholder' => 'Requester Name'));
      }
      echo $this->Form->input('department_id',
                              array(
                                    'empty' => '(choose one)', 
                                    'label' => 'Department', 
                                    'class' => 'form-control autocomplete'));    
      
      echo $this->Form->submit(
          'Search', 
          array('class' => 'hidden')
      );
      
    ?>
	  </div>
	</div>
	<!-- @todo ADD STATUS TO TABLE -->
	<div class="col-sm-9">
	  <table class="table table-striped">
      <thead>
          <tr>
            <th style="width: 15px;"></th>
            <th class="col-sm-1"><?php echo $this->Paginator->sort('id', '#');?></th>
            <th class="col-sm-2"><?php echo $this->Paginator->sort('date_received', 'Received');?></th>
            <th class="col-sm-4"><?php echo $this->Paginator->sort('text', 'Request');?></th>
            <th><?php echo $this->Paginator->sort('Department.name', 'Department');?></th>
            <th><?php echo $this->Paginator->sort('', 'Point of Contact');?></th>
            <?php if ($this->Session->read('Auth.User')): ?>
            <th><?php echo $this->Paginator->sort('due_date', 'Due');?></th>
            <th><?php echo $this->Paginator->sort('User.Alias', 'Requester Name');?></th>
            <?php endif;?>

          </tr>
      </thead>
      <tbody>
        <?php 
          foreach ($results as $result){
            //let's see if the request is due soon
            $today = date("Y-m-d");
            $due_date = $result["Request"]["due_date"];
            $overdue = false;
            $dueSoon = false;
            if($today > $due_date){
              $overdue = true;
            }
            if($today2 >= $due_date){
              $dueSoon = true;
            }
            echo "<tr>\n";
            if ($this->Session->read('Auth.User') && $result["Request"]["status_id"] != 2 && $dueSoon && !$overdue){ //due soon
              $statusClass = "warning"; 
            }elseif($this->Session->read('Auth.User') && $result["Request"]["status_id"] != 2 && $overdue){ // overdue
              $statusClass = "danger"; 
            }elseif($result["Request"]["status_id"] == 2){ //closed
              $statusClass = "closed"; 
            }else{
              $statusClass = "success"; 
            }
            printf("<td class=\"status status-%s\"></td>\n",$statusClass);
            printf("<td>%d</td>\n",$result["Request"]["id"]);
            printf("<td>%s</td>\n",$this->Time->format('M jS, Y',$result["Request"]["date_received"]));
            printf("<td>%s</td>\n",$this->Html->link($this->Text->truncate($result["Request"]["text"],100,
                                                        array(
                                                            'ellipsis' => '...',
                                                            'exact' => false
                                                        )), array('action' => 'view', $result["Request"]["id"])));
            printf("<td>%s</td>\n",$result["Department"]["name"]);
            printf("<td>POC</td>\n");
            if ($this->Session->read('Auth.User')){
              printf("<td>%s</td>\n",$this->Time->format('M jS, Y',  $result["Request"]["due_date"]));
              printf("<td>%s</td>\n",$result["Requester"]["alias"]); 
            }
            echo "</tr>\n";
          }
        ?>
      </tbody>
	  </table>
	  <ul class="pagination pull-right">
	    <li>
	      <?php echo $this->Paginator->prev(
            __('Previous'),
            array(),
            null,
            array('style' => 'display: none')
          );
        ?>
      </li>
      <li>
        <?php echo $this->Paginator->next(
            __('Next'),
            array(),
            null,
            array('style' => 'display: none')
          );
        ?>
      </li>
    </ul>
    

	  <p><?php echo $this->Paginator->counter('Page {:page} of {:pages}, showing {:current} records out of {:count} total');?></p>
	</div>
</div>
