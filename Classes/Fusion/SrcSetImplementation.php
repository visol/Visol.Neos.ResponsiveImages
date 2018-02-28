<?php
namespace Visol\Neos\ResponsiveImages\Fusion;

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
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\AssetService;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Media\Domain\Service\ThumbnailService;

/**
 * Render the srcset attribute with responsive images. Accepts mostly the same parameters as the uri.image ViewHelper of the Neos.Media package:
 * asset, maximumWidth, maximumHeight, allowCropping, ratio.
 *
 */
class SrcSetImplementation extends AbstractFusionObject
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
     * Asset
     *
     * @return AssetInterface
     */
    public function getAsset()
    {
        return $this->fusionValue('asset');
    }

    /**
     * Sizes
     *
     * @return array
     */
    public function getSizes()
    {
        return $this->fusionValue('sizes');
    }

    /**
     * Ratio
     *
     * @return float
     */
    public function getRatio()
    {
        return $this->fusionValue('ratio');
    }

    /**
     * MaximumWidth
     *
     * @return integer
     */
    public function getMaximumWidth()
    {
        return $this->fusionValue('maximumWidth');
    }

    /**
     * MaximumHeight
     *
     * @return integer
     */
    public function getMaximumHeight()
    {
        return $this->fusionValue('maximumHeight');
    }

    /**
     * AllowCropping
     * Implicitly activated on fixed ratio
     *
     * @return boolean
     */
    public function getAllowCropping()
    {
        if ($this->getRatio()) {
            return true;
        }

        return $this->fusionValue('allowCropping');
    }

    public function getQuality()
    {
        return $this->fusionValue('quality');
    }

    /**
     * Returns a processed image path
     *
     * @return string
     * @throws \Exception
     */
    public function evaluate()
    {
        $asset = $this->getAsset();
        $maximumWidth = $this->getMaximumWidth();
        $maximumHeight = $this->getMaximumHeight();
        $ratio = $this->getRatio();

        $sizes = $this->getSizes();

        if (!is_array($sizes) || !count($sizes) > 0) {
            throw new \Exception('No sizes defined.', 1519837126);
        }

        if (!$asset instanceof AssetInterface) {
            throw new \Exception('No asset given for rendering.', 1415184217);
        }

        if ($asset instanceof ImageInterface) {
            $assetWidth = $asset->getWidth();
            $assetHeight = $asset->getHeight();
        }

        $request = $this->getRuntime()->getControllerContext()->getRequest();

        $srcSetData = [];
        foreach ($sizes as $size) {
            $currentWidth = null;
            $currentMaximumWidth = $size;
            $currentHeight = null;
            $currentMaximumHeight = null;
            $currentAllowCropping = false;

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

            $thumbnailConfiguration = new ThumbnailConfiguration($currentWidth, $currentMaximumWidth, $currentHeight, $currentMaximumHeight, $currentAllowCropping, false, false, $this->getQuality());
            $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset($asset, $thumbnailConfiguration, $request);

            if ($thumbnailData === null) {
                continue;
            }

            $srcSetData[] = $thumbnailData['src'] . ' ' . $thumbnailData['width'] . 'w ' . $thumbnailData['height'] . 'h ';
        }

        return implode(', ', $srcSetData);
    }
}
