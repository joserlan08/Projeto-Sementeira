<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Plant $plant
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Plants'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="plants form content">
            <?= $this->Form->create($plant) ?>
            <fieldset>
                <legend><?= __('Add Plant') ?></legend>
                <?php
                    echo $this->Form->control('nome');
                    echo $this->Form->control('description');
                    echo $this->Form->control('price');
                    echo $this->Form->control('stock');
                    echo $this->Form->control('image');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
