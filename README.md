# Visol.Neos.ResponsiveImages

This Neos package helps using npm's lazysizes strategy to implement responsive images. See https://www.npmjs.com/package/lazysizes


## Installation

To install the package correctly, go to your theme package (e.g. `Packages/Sites/Visol.Site`) and run the following command:

  ```
  composer require visol/neos-responsiveimages --no-update
  ```

* Install lazysizes. E.g. with npm

  ```
  npm install lazysizes --save
  ```

* Add the JavaScript sources to your main script
  ```
  import 'lazysizes/plugins/parent-fit/ls.parent-fit.min';
  import 'lazysizes/plugins/bgset/ls.bgset.min';
  import 'lazysizes/lazysizes.min';
  ``` 


## Quick Start

Just replace usages of the `Neos.Fusion:Image` prototype with `Visol.Neos.ResponsiveImages:ImageTag`. e.g.

```
    image = Visol.Neos.ResponsiveImages:ImageTag {
        asset = ${q(event).property('teaserImage')}
        ratio = 1.46
    }
```


Or use the ViewHelper provided. e.g.

```
{namespace responsiveImages=Visol\Neos\ResponsiveImages\ViewHelpers}

<responsiveImages:image image="{item.properties.sliderImage}" ratio="1.89583" />
```


## Configuration

Adjust the desired image sizes in your `Settings.yaml`

```
Visol:
  Neos:
    ResponsiveImages:
      SizesPresets:
        Default:
          - 16
          - 48
          - 96
          - 160
          - 320
          - 480
          - 640
          - 960
          - 1024
          - 1440
          - 1920
          - 2560
          - 3840
          - 5120
```


## Usage with background images

Use `Visol.Neos.ResponsiveImages:SrcSet` prototype to generate srcset-Attribute

```
    imageUri = Visol.Neos.ResponsiveImages:SrcSet {
        asset = ${q(offer).property('image')}
        ratio = 0.91
    }
```

Add class `lazyload` and data-attributes

```html
<div class="lazyload" data-bgset={props.imageUri} data-sizes="auto">
```


### Credits

https://www.npmjs.com/package/lazysizes

visol digitale Dienstleistungen GmbH, www.visol.ch
