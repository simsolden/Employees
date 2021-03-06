<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Salary $salary
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $salary->emp_no],
                ['confirm' => __('Are you sure you want to delete # {0}?', $salary->emp_no), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Salaries'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="salaries form content">
            <?= $this->Form->create($salary) ?>
            <fieldset>
                <legend><?= __('Edit Salary') ?></legend>
                <?php
                    echo $this->Form->control('salary');
                    echo $this->Form->control('to_date');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
