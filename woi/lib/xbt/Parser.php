<?hh // strict

namespace Lib\xbt;

class Parser
{
    protected TokenStream $stream;
    protected Map<string, BlockNode> $blocks;
    protected ?string $latestBlockName;
    protected int $depth;
    protected int $blockDepth;

    public function __construct(TokenStream $stream)
    {
        $this->stream = $stream;
        $this->blocks = Map {};
        $this->depth = 0;
        $this->blockDepth = 0;
    }

    public function getStream() : TokenStream
    {
        return $this->stream;
    }

    public function getBlocks() : Map<string, BlockNode>
    {
        return $this->blocks;
    }

    public function addBlock(BlockNode $block): void
    {
        $name = $block->getNameAttribute();

        if ($this->blocks->containsKey($name)) {
            throw new SyntaxError("Duplicate block name \"$name\" detected");
        }

        $this->blocks[$name] = $block;
    }

    public function parse() : Template
    {
        $stream = $this->getStream();
        $stream->skip(T_WHITESPACE);
        $template = $this->parseTag(':xbt:template');
        $stream->skip(T_WHITESPACE);
        $stream->expect(Token::T_XHP_EOF);
        return $template;
    }

    /**
     * Parse any tag, including the xbt:template
     */
    public function parseTag() : Node
    {
        $tokens = [];

        $stream = $this->getStream();

        if (!$stream->match(T_XHP_TAG_LT)) {
            throw new SyntaxError('Tag is not preperly opened');
        }

        $label = $stream->peek(1);

        if (!$label->match(T_XHP_LABEL)) {
          throw new SyntaxError(sprintf('Tag does not have a valid name: %s at Line: %d, Near: %s', $label->value, $stream->row, $stream->text));
        }

        switch ($label->value) {
        case ':xbt:template':
            return $this->parseTemplateTag();

        case ':xbt:block':
            return $this->parseBlockTag();

        case ':xbt:parent':
            return $this->parseParentTag();

        case ':xbt:include':
            return $this->parseIncludeTag();

        default:
            return $this->parseGenericTag();
        }

    }

    public function parseChildren(string $name) : NodeList
    {
        $stream = $this->getStream();

        $nodes = Vector<Node>{};

        $properlyClosed = false;

        while (!$stream->isEOF()) {
            if ($stream->match(T_XHP_TAG_LT)) {
                if ($this->matchClosingTag($name)) {
                    $properlyClosed = true;
                    break;
                } else {
                    $nodes[] = $this->parseTag();
                }
            } elseif ($stream->match(T_XHP_TEXT)) {
                $nodes[] = $this->parseText();
            } elseif ($stream->match(Token::T_XHP_BRACE_OPEN)) {
                $nodes[] = $this->parseDelimitedExpression();
            } else {
              throw new SyntaxError(sprintf('Inconsistent parser state: child component must either be a tag, a text, or an expression at Found: %s at Line: %d, Near: %s', token_name($stream->getCurrent()->type), $stream->row, $stream->text));
            }
        }

        if (!$properlyClosed) {
            throw new SyntaxError("Inconsistent parser state: tag $name is not properly closed");
        }

        return new NodeList($nodes);
    }

    public function matchClosingTag(string $name) : bool
    {
        $stream = $this->getStream();

        $next = $stream->peek(1);

        if ($next->match(Token::T_XHP_TAG_SLASH)) {

            $n = 0;
            do {
                $next = $stream->peek(2 + $n);
                $n += 1;
            } while ($next->match(T_WHITESPACE));

            if ($next->match(T_XHP_LABEL, $name)) {
                $stream->expect(T_XHP_TAG_LT);
                $stream->expect(Token::T_XHP_TAG_SLASH);
                $stream->skip(T_WHITESPACE);
                $stream->expect(T_XHP_LABEL, $name);
                $stream->skip(T_WHITESPACE);
                $stream->expect(T_XHP_TAG_GT);
                $this->depth -= 1;
                if ($name === ':xbt:block' && $this->blockDepth > 0) {
                    $this->blockDepth -= 1;
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Parse an expression: {...}
     */
    public function parseDelimitedExpression() : DelimitedExpressionNode
    {
        $depth = 0;

        $tokens = [];

        $stream = $this->getStream();

        $tokens[] = $stream->expect(Token::T_XHP_BRACE_OPEN);

        $depth += 1;

        while (!$stream->isEOF()) {

            if ($stream->match(Token::T_XHP_BRACE_OPEN)) {
                $depth += 1;
            }

            if ($stream->match(Token::T_XHP_BRACE_CLOSE)) {
                $depth -= 1;
            }

            $tokens[] = $stream->getCurrent();

            $stream->next();

            if ($depth === 0) {
                break;
            }
        }

        if ($depth !== 0) {
            throw new SyntaxError('Delimited expression is not properly closed');
        }

        $expression = '';

        foreach ($tokens as $token) {
            // support sementara untuk dapat mendukung XHP non XBT
            if ($token->type === T_XHP_LABEL) {
                $expression .= substr($token->value, 1);
            } else {
                $expression .= $token->value;
            }
        }

        return new DelimitedExpressionNode($expression);
    }

    public function parseText() : TextNode
    {
        $stream = $this->getStream();

        $token = $stream->expect(T_XHP_TEXT);

        if ($stream->isEOF()) {
            throw new SyntaxError('Text is not closed properly');
        }

        return new TextNode($token->value);
    }

    public function parseIncludeTag() : IncludeNode
    {
        $stream = $this->getStream();

        $stream->expect(T_XHP_TAG_LT);

        $stream->expect(T_XHP_LABEL, ':xbt:include');

        $stream->skip(T_WHITESPACE);

        $attributes = $this->parseTagAttributes();

        $stream->expect(Token::T_XHP_TAG_SLASH);

        $stream->expect(T_XHP_TAG_GT);

        return new IncludeNode($attributes);
    }

    protected function parseTemplateTag() : Template
    {
        $stream = $this->getStream();

        $stream->expect(T_XHP_TAG_LT);

        $this->assertTemplateNotNested();

        $name = $stream->expect(T_XHP_LABEL, ':xbt:template');

        $stream->skip(T_WHITESPACE);

        $attributes = $this->parseTagAttributes();

        $hasChildren = !$stream->consume(Token::T_XHP_TAG_SLASH);

        $stream->expect(T_XHP_TAG_GT);

        $children = new NodeList(Vector<Node> {});

        if ($hasChildren) {
            $this->depth += 1;

            $children = $this->parseChildren($name->value);
        }

        return new Template($attributes, $children, $this->getBlocks());
    }

    protected function parseBlockTag() : BlockNode
    {
        $stream = $this->getStream();

        $stream->expect(T_XHP_TAG_LT);

        $name = $stream->expect(T_XHP_LABEL, ':xbt:block');

        $stream->skip(T_WHITESPACE);

        $attributes = $this->parseTagAttributes();

        $this->latestBlockName = (string) $attributes[':name'];

        $hasChildren = !$stream->consume(Token::T_XHP_TAG_SLASH);

        $stream->expect(T_XHP_TAG_GT);

        $children = new NodeList(Vector<Node> {});

        if ($hasChildren) {

            $this->depth += 1;

            $this->blockDepth += 1;

            $children = $this->parseChildren($name->value);
        }

        $block = new BlockNode($attributes, $children);

        $this->addBlock($block);

        return $block;
    }

    protected function parseParentTag() : ParentNode
    {
        $stream = $this->getStream();

        $stream->expect(T_XHP_TAG_LT);

        $stream->expect(T_XHP_LABEL, ':xbt:parent');

        $stream->skip(T_WHITESPACE);

        $attributes = $this->parseTagAttributes();

        if ($this->blockDepth === 0) {
            throw new SyntaxError('Parent tag must be inside of a block tag');
        }

        if (!$stream->consume(Token::T_XHP_TAG_SLASH)) {
            throw new SyntaxError('Parent tag must not have children');
        }

        $stream->expect(T_XHP_TAG_GT);

        return new ParentNode($this->latestBlockName, $attributes);
    }

    protected function parseGenericTag() : TagNode
    {
        $stream = $this->getStream();

        $stream->expect(T_XHP_TAG_LT);

        $name = $stream->expect(T_XHP_LABEL);

        $stream->skip(T_WHITESPACE);

        $attributes = $this->parseTagAttributes();

        $hasChildren = !$stream->consume(Token::T_XHP_TAG_SLASH);

        $stream->expect(T_XHP_TAG_GT);

        $children = new NodeList(Vector<Node> {});

        if ($hasChildren) {

            $this->depth += 1;

            $children = $this->parseChildren($name->value);
        }

        return new TagNode($name->value, $attributes, $children);
    }

    public function assertTemplateNotNested()
    {
        $stream = $this->getStream();

        if ($stream->match(T_XHP_LABEL, ':xbt:template') && $this->depth > 0) {
            throw new SyntaxError('Template tag cannot be nested');
        }
    }

    public function parseTagAttributes() : TagAttributes
    {
        $attributes = Map<string, ExpressionNode> {};

        $stream = $this->getStream();

        while (!$stream->match(T_XHP_TAG_GT) && !$stream->match(Token::T_XHP_TAG_SLASH)) {

            if ($stream->isEOF()) {
                throw new SyntaxError('Tag attribute is not properly closed');
            }

            $key = $stream->expect(T_XHP_LABEL);
            $stream->skip(T_WHITESPACE);
            $stream->expect(Token::T_XHP_ATTRIBUTE_EQUAL);
            $stream->skip(T_WHITESPACE);

            if ($stream->match(T_XHP_TEXT)) {
                $value = new StringNode($stream->expect(T_XHP_TEXT)->value);
            } elseif ($stream->match(Token::T_XHP_BRACE_OPEN)) {
                $value = $this->parseDelimitedExpression();
            } else {
                throw new SyntaxError('Attribute values need to be a valid expression or a quoted literal string');
            }

            $attributes[$key->value] = $value;
            $stream->skip(T_WHITESPACE);
        }

        return new TagAttributes($attributes);
    }
}

