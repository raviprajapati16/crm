<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-estimates">
    <div class="panel-body">
        <h4 class="no-margin section-text">Media</h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <?php if (!empty($media) && is_array($media)): ?>
            <div class="row">
                <?php foreach ($media as $item): ?>
                    <?php
                    $link = '';
                    $media_file = $item['media_file'];
                    $icon_class = '';

                    // Generate appropriate link and icon based on media type
                    if ($item['rel_type'] == 'product_presentation' && !empty($item['hash'])) {
                        $link = site_url('product_presentation/view/' . $item['hash']);
                        $icon_class = 'fa-file-powerpoint-o';
                    } elseif ($item['rel_type'] == 'brochure' && !empty($item['hash'])) {
                        $link = site_url('brochure/view/' . $item['hash']);
                        $icon_class = 'fa-file-pdf-o';
                    } elseif ($item['rel_type'] == 'tutorial' && !empty($media_file)) {
                        $link = $media_file;
                        $icon_class = 'fa-play-circle';
                    }
                    ?>

                    <div class="col-md-3 col-sm-4 col-xs-6">
                        <div class="media-card" onclick="openMedia('<?php echo $link; ?>')">
                            <div class="media-icon">
                                <i class="fa <?php echo $icon_class; ?> fa-3x"></i>
                            </div>
                            <div class="media-info">
                                <h5 class="media-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="media-type"><?php echo ucfirst(str_replace('_', ' ', $item['rel_type'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center mtop30">
                <i class="fa fa-file-o fa-5x text-muted"></i>
                <h4 class="text-muted"><?php echo _l('No media found'); ?></h4>
                <p class="text-muted"><?php echo _l('No media items have been shared with you yet.'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CSS for media cards -->
<style>
    .media-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .media-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-color: #337ab7;
    }

    .media-icon {
        margin-bottom: 15px;
        color: #337ab7;
    }

    .media-icon .fa-file-powerpoint-o {
        color: #d04437;
    }

    .media-icon .fa-file-pdf-o {
        color: #ff4444;
    }

    .media-icon .fa-play-circle {
        color: #ff0000;
    }

    .media-info {
        text-align: center;
    }

    .media-title {
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 8px 0;
        color: #333;
        word-wrap: break-word;
        line-height: 1.3;
    }

    .media-type {
        font-size: 12px;
        color: #666;
        margin: 0 0 5px 0;
        text-transform: capitalize;
    }

    .media-date {
        font-size: 11px;
        color: #999;
    }

    @media (max-width: 768px) {
        .media-card {
            padding: 15px;
            margin-bottom: 15px;
        }

        .media-icon .fa {
            font-size: 2em !important;
        }

        .media-title {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .col-xs-6 {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>

<!-- JavaScript for handling media clicks -->
<script>
    function openMedia(link) {
        if (link && link.trim() !== '') {
            window.open(link, '_blank');
        }
    }
</script>