<?php
namespace Visol\Neos\ResponsiveImages\ViewHelpers;

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
use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;
use Neos\Media\Domain\Model\ImageInterface;
use Visol\Neos\ResponsiveImages\Service\SrcSetService;

/**
 * Renders an <img> HTML tag from a given Neos.Media's image instance
 *
 */
class ImageViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @Flow\Inject
     * @var SrcSetService
     */
    protected $srcSetService;

    /**
     * @Flow\InjectConfiguration(package="Visol.Neos.ResponsiveImages.SizesPresets")
     * @var array
     */
    protected $sizesPresets = array();

    /**
     * name of the tag to be created by this view helper
     *
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', true);
    }

    /**
     * Renders an HTML img tag with a thumbnail image, created from a given image.
     *
     * @param ImageInterface $image The image to be rendered as an image
     * @param integer $width Desired width of the image
     * @param integer $maximumWidth Desired maximum width of the image
     * @param integer $height Desired height of the image
     * @param integer $maximumHeight Desired maximum height of the image
     * @param boolean $allowCropping Whether the image should be cropped if the given sizes would hurt the aspect ratio
     * @param boolean $allowUpScaling Whether the resulting image size might exceed the size of the original image
     * @param boolean $async Return asynchronous image URI in case the requested image does not exist already
     * @param string $preset Preset used to determine image configuration
     * @param integer $quality Quality of the image
     * @return string an <img...> html tag
     */
    public function render(ImageInterface $image = null, $ratio = null, $maximumWidth = null, $maximumHeight = null, $allowCropping = false, $quality = null)
    {
        $sizes = $this->sizesPresets['Default'];

        $srcSetString = $this->srcSetService->getSrcSetAttribute($image, $ratio, $maximumWidth, $maximumHeight, $allowCropping, $quality, $sizes, null);

        $classNames = ['lazyload'];
        if (isset($this->arguments['class'])) {
            $classNames[] = $this->arguments['class'];
        }

        $this->tag->addAttributes([
            'class' => implode(' ', $classNames),
            'data-sizes' => 'auto',
            'data-srcset' => $srcSetString,
        ]);

        // alt argument must be set because it is required (see $this->initializeArguments())
        if ($this->arguments['alt'] === '') {
            // has to be added explicitly because empty strings won't be added as attributes in general (see parent::initialize())
            $this->tag->addAttribute('alt', '');
        }

        return $this->tag->render();
    }
}
