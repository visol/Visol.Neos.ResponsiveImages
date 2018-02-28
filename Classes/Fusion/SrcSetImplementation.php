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
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Visol\Neos\ResponsiveImages\Service\SrcSetService;

/**
 * Render the srcset attribute with responsive images. Accepts mostly the same parameters as the uri.image ViewHelper of the Neos.Media package:
 * asset, maximumWidth, maximumHeight, allowCropping, ratio.
 *
 */
class SrcSetImplementation extends AbstractFusionObject
{

    /**
     * @Flow\Inject
     * @var SrcSetService
     */
    protected $srcSetService;

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
        $ratio = $this->getRatio();
        $maximumWidth = $this->getMaximumWidth();
        $maximumHeight = $this->getMaximumHeight();
        $allowCropping = $this->getAllowCropping();
        $quality = $this->getQuality();
        $sizes = $this->getSizes();
        $request = $this->getRuntime()->getControllerContext()->getRequest();

        return $this->srcSetService->getSrcSetAttribute($asset, $ratio, $maximumWidth, $maximumHeight, $allowCropping, $quality, $sizes, $request);
    }
}
