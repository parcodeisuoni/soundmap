<?php

?>


<div <?php post_class('marker-window', $id) ?>>
    <div class="post-title"><h3><a href="<?php echo get_permalink($id);?>"><?php echo get_the_title($id); ?></a></h3></div>
    <div class="post-content">
        <?php echo apply_filters('the_content',get_the_content()) ?>
        <?php if(isset($mark->autor) && $mark->autor){ ?> <p class="marker-info"><?php echo __('Author', 'soundmap') . ': ' . $mark->autor; ?></br><?php } ?>
        <hr class="clear">
        <audio class="soundmap-audio-player not-processed" src="<?php echo $mark->files[0]['url'] ?>"></audio>
        <div class="marker-info">
        <?php the_tags(__('Tags','soundmap') . ': ', ' | ', '</br>'); ?>
        <?php echo __('Categories','soundmap') . ': '; the_category(' | '); ?>
        </div>
    </div>


</div>
