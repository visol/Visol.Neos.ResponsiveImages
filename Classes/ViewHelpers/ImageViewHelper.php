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
    protected $sizesPresets = [];

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
        $this->registerArgument('image', ImageInterface::class, 'The image to be rendered as an image');
        $this->registerArgument('ratio', 'float', 'The aspect ratio for the image');
        $this->registerArgument('maximumWidth', 'integer', 'Desired maximum width of the image');
        $this->registerArgument('maximumHeight', 'integer', 'Desired maximum height of the image');
        $this->registerArgument(
            'allowCropping',
            'boolean',
            'Whether the image should be cropped if the given sizes would hurt the aspect ratio',
            false,
            false
        );
        $this->registerArgument('quality', 'integer', 'Quality of the image, from 0 to 100');
    }

    /**
     * Renders an HTML img tag with a thumbnail image, created from a given image.
     * @return string an <img...> html tag
     *
     * @throws \Exception
     */
    public function render()
    {
        $sizes = $this->sizesPresets['Default'];

        $srcSetString = $this->srcSetService->getSrcSetAttribute(
            $this->arguments['image'],
            $this->arguments['ratio'],
            $this->arguments['maximumWidth'],
            $this->arguments['maximumHeight'],
            $this->arguments['allowCropping'],
            $this->arguments['quality'],
            $sizes,
            null
        );

        $classNames = ['lazyload'];
        if (isset($this->arguments['class'])) {
            $classNames[] = $this->arguments['class'];
        }

        $this->tag->addAttributes(
            [
                'class' => implode(' ', $classNames),
                'data-sizes' => 'auto',
                'data-srcset' => $srcSetString,
            ]
        );

        // alt argument must be set because it is required (see $this->initializeArguments())
        if ($this->arguments['alt'] === '') {
            // has to be added explicitly because empty strings won't be added as attributes in general (see parent::initialize())
            $this->tag->addAttribute('alt', '');
        }

        return $this->tag->render();
    }
}
