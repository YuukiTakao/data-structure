<?php


/**
 * trie木
 * https://ja.wikipedia.org/wiki/%E3%83%88%E3%83%A9%E3%82%A4_(%E3%83%87%E3%83%BC%E3%82%BF%E6%A7%8B%E9%80%A0)
 *
 * 参考
 *
 * C++でのTrie木の実装
 * https://algo-logic.info/trie-tree/
 *
 * PHPでのTrie木の実装
 * https://rightcode.co.jp/blog/information-technology/trie-fast-dictionary-implementation-1
 */

class Solution
{
    /**
     * @param String[] $strs
     * @return String
     */
    function longestCommonPrefix($strs) {
        $start = memory_get_usage();
        if (count($strs) === 1) {
            return $strs[0];
        }

        $trie = new Trie('a', 26);
        foreach ($strs as $str) {
            $trie->insert($str);
        }
        $res = $trie->longestCommonPrefix($strs);
        $end = memory_get_usage();
        var_dump(($end - $start) / 1024 . 'kb' );
        return $res;
    }
}

class Trie
{
    public $nodes;
    public $baseChar;
    public $size;

    public function __construct(string $baseChar, int $size)
    {
        $root = new TrieNode($size);
        $this->nodes    = [$root];
        $this->baseChar = $baseChar;
        $this->size     = $size;
    }

    private function addNode($node)
    {
        array_push($this->nodes, $node);
        return count($this->nodes) - 1;
    }

    private function getCharCode($ch)
    {
        return ord($ch) - ord($this->baseChar);
    }

    public function insert($word, $charIndex = 0, $nodeIndex = 0)
    {
        $charCode = $this->getCharCode(str_split($word)[$charIndex]);
        $nextNodeIndex = $this->nodes[$nodeIndex]->dict[$charCode];
        if ($nextNodeIndex == -1) {
            $newNode = new TrieNode($this->size);
            $nextNodeIndex = $this->addNode($newNode);
            $this->nodes[$nodeIndex]->dict[$charCode] = $nextNodeIndex;
            $this->nodes[$nodeIndex]->charNum++;
        }

        if ($charIndex == (strlen($word) - 1)) {
            $this->nodes[$nextNodeIndex]->word = $word;
        } else {
            $this->insert($word, $charIndex+1, $nextNodeIndex);
        }
    }

    public function query($word)
    {
        $nodeIndex = 0;

        $word = str_split($word);

        foreach($word as $ch) {
            $charCode = $this->getCharCode($ch);
            $tmpNode = $this->nodes[$nodeIndex];

            $nodeIndex = $tmpNode->dict[$charCode];
            if ($nodeIndex == -1) {
                return null;
            }
        }
        return $this->nodes[$nodeIndex]->word;
    }

    private function getNodeNumCommonPrefix(): string
    {
        $i = 0;
        foreach ($this->nodes as $key => $node) {
            if ($node->charNum === 1) {
                $i++;
            } else {
                break;
            }
        }
        return $i;
    }

    private function getStrByStrCode(int $code): string
    {
        $asciiCode = $code + ord($this->baseChar);

        return chr($asciiCode);
    }

    public function longestCommonPrefix(array $strs): string
    {
        // ノードを共有している単語が1で、一番深いノードを返す
        $targetNodeNum = $this->getNodeNumCommonPrefix();
        if ($targetNodeNum == 0) {
            return '';
        }

        $strCodes = [];
        for ($i = 0; $i < $targetNodeNum; $i++) {
            $filterd = array_filter(
                $this->nodes[$i]->dict, function($v) { return $v >= 0;}
            );
            $strCodes[] = array_keys($filterd)[0];
        }

        $result = [];
        foreach ($strCodes as $code) {
            $result[] = $this->getStrByStrCode((int)$code);
        }

        return implode($result);
    }
}

class TrieNode
{
    public $dict;
    public $word;
    public $charNum; // ノードを共有している単語数を保持

    public function __construct(int $size, $word = null)
    {
        $this->word    = $word;
        $this->dict    = array_fill(0, $size, -1);
        $this->charNum = 0;
    }
}