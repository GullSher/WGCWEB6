<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Crawler\UrlExtractor\Mapping\HtmlUrlExtractorMapping as BaseMapping;

final class HtmlUrlExtractorMapping extends BaseMapping
{
    /**
     * @var mixed[]|null
     */
    private $cachedMapping;

    /**
     * @var mixed[]|null
     */
    private $cachedSrcsetAttributes;

    public function mapping() : array
    {
        if ($this->cachedMapping === null) {
            $this->cachedMapping = parent::mapping();
            $this->cachedMapping['img'] = \array_merge(
                $this->cachedMapping['img'],
                ['data-srcset', 'data-wpfc-original-srcset']
            );
            $this->cachedMapping = \apply_filters('staatic_html_mapping_tags', $this->cachedMapping);
        }

        return $this->cachedMapping;
    }

    public function srcsetAttributes() : array
    {
        if ($this->cachedSrcsetAttributes === null) {
            $this->cachedSrcsetAttributes = \array_merge(
                parent::srcsetAttributes(),
                ['data-srcset', 'data-wpfc-original-srcset']
            );
            $this->cachedSrcsetAttributes = \apply_filters(
                'staatic_html_mapping_srcset',
                $this->cachedSrcsetAttributes
            );
        }

        return $this->cachedSrcsetAttributes;
    }
}
