<?php
namespace Visol\Neos\ResponsiveImages\Service;

/*
 * This file is part of the Visol.Neos.ResponsiveImages package.
 *
 * (c) visol digitale Dienstleistungen GmbH, www.visol.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;

/**
 * Render the srcset attribute with responsive images. Accepts mostly the same parameters as the uri.image ViewHelper of the Neos.Media package:
 * asset, maximumWidth, maximumHeight, allowCropping, ratio.
 *
 */
class SrcSetService
{

    /**
     * Resource publisher
     *
     * @Flow\Inject
     * @var AssetService
     */
    protected $assetService;


    /**
     * @Flow\Inject
     * @var ThumbnailService
     */
    protected $thumbnailService;


    /**
     * Returns a processed image path
     *
     * @param AssetInterface $asset
     * @param float $ratio
     * @param int $maximumWidth
     * @param int $maximumHeight
     * @param boolean $allowCropping
     * @param int $quality
     * @param array $sizes
     * @param RequestInterface $request
     * @return string
     * @throws \Exception
     */
    public function getSrcSetAttribute($asset, $ratio, $maximumWidth, $maximumHeight, $allowCropping, $quality, array $sizes, $request = null)
    {
        if (!is_array($sizes) || !count($sizes) > 0) {
            throw new \Exception('No sizes defined.', 1519837126);
        }

        if (!$asset instanceof AssetInterface) {
            throw new \Exception('No asset given for rendering.', 1519844659);
        }

        if ($asset instanceof ImageInterface) {
            $assetWidth = $asset->getWidth();
            $assetHeight = $asset->getHeight();
        }

        $srcSetData = [];
        foreach ($sizes as $size) {
            $currentWidth = null;
            $currentMaximumWidth = $size;
            $currentHeight = null;
            $currentMaximumHeight = null;
            $currentAllowCropping = $allowCropping;

            if ($currentMaximumWidth > $assetWidth) {
                continue;
            }

            if (isset($maximumWidth) && $currentMaximumWidth > $maximumWidth) {
                continue;
            }

            if ($ratio) {
                $currentWidth = $currentMaximumWidth;
                $currentMaximumHeight = $size / $ratio;
                $currentHeight = $currentMaximumHeight;
                $currentAllowCropping = true;

                if ($currentMaximumHeight > $assetHeight) {
                    continue;
                }

                if (isset($maximumHeight) && $currentMaximumHeight > $maximumHeight) {
                    continue;
                }
            }

            $thumbnailConfiguration = new ThumbnailConfiguration($currentWidth, $currentMaximumWidth, $currentHeight, $currentMaximumHeight, $currentAllowCropping, false, false, $quality);
            $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($asset, $thumbnailConfiguration, $request);

            if ($thumbnailData === null) {
                continue;
            }

            $srcSetData[] = $thumbnailData['src'] . ' ' . $thumbnailData['width'] . 'w ' . $thumbnailData['height'] . 'h ';
        }

        return implode(', ', $srcSetData);
    }
}
