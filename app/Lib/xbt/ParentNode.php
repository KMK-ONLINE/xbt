<?hh // strict

namespace App\Lib\xbt;

class ParentNode implements Node
{
    protected string $blockName;
    protected TagAttributes $attributes;

    public function __construct(string $blockName, TagAttributes $attributes)
    {
        $this->name = ':xbt:parent';
        $this->blockName = $blockName;
        $this->attributes = $attributes;
    }

    public function getBlockName() : string
    {
        return $this->blockName;
    }

    public function render() : string
    {
        $parent = 'parent::block_' . $this->getBlockName();

        return '{is_callable(\'' . $parent . '\') ? ' . $parent . '() : null}';
    }
}

