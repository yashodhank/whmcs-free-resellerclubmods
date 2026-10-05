<?php
if (!defined("WHMCS") && PHP_SAPI !== "cli") {
    exit("This file cannot be accessed directly");
}
if (!class_exists("RcmToolsPagination")) {
    class RcmToolsPagination
    {
        public $page = 1;
        public $perPage = 20;
        public $showFirstAndLast = false;
        public $maxPageLinks = 0;
        protected $length = 0;
        protected $pages = 0;
        protected $start = 0;
        public function __construct($perPage = 20)
        {
            if (is_numeric($perPage) && 0 < (int) $perPage) {
                $this->perPage = (int) $perPage;
            }
        }
        public function generate($array, $perPage = 20)
        {
            if (!empty($perPage) && is_numeric($perPage) && 0 < (int) $perPage) {
                $this->perPage = (int) $perPage;
            }
            $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
            if ($page < 1) {
                $page = 1;
            }
            $this->page = $page;
            $userParam = isset($_GET["user"]) ? (string) $_GET["user"] : "";
            if ($userParam === "rcuser-vs-whmcsusers") {
                $this->length = isset($GLOBALS["recsindb_length"]) && is_numeric($GLOBALS["recsindb_length"]) ? (int) $GLOBALS["recsindb_length"] : 0;
                $this->pages = 0 < $this->perPage ? (int) ceil($this->length / $this->perPage) : 0;
                $this->start = 0;
                if (!is_array($array)) {
                    $array = (array) $array;
                }
                return array_slice($array, 0, $this->perPage);
            }
            if (!is_array($array)) {
                $array = (array) $array;
            }
            $this->length = count($array);
            $this->pages = 0 < $this->perPage ? (int) ceil($this->length / $this->perPage) : 0;
            if (0 < $this->pages && $this->pages < $this->page) {
                $this->page = $this->pages;
            }
            $this->start = ($this->page - 1) * $this->perPage;
            if ($this->start < 0) {
                $this->start = 0;
            }
            return array_slice($array, $this->start, $this->perPage);
        }
        public function links()
        {
            global $_ADDONLANG;
            $plinks = [];
            $links = [];
            $slinks = [];
            $queryURL = "";
            if (!empty($_GET) && is_array($_GET)) {
                foreach ($_GET as $key => $value) {
                    if ($key === "page") {
                    } else {
                        $queryURL .= "&" . rawurlencode($key) . "=" . rawurlencode((string) $value);
                    }
                }
            }
            if ($this->pages <= 1) {
                return "";
            }
            $script = isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "";
            $script = htmlspecialchars($script, ENT_QUOTES, "UTF-8");
            if (1 < $this->page) {
                if ($this->showFirstAndLast) {
                    $plinks[] = "<a href=\"" . $script . "?page=1" . $queryURL . "\">&laquo;&laquo; " . $_ADDONLANG["pagefirst"] . " </a>";
                }
                $prev = $this->page - 1;
                $plinks[] = "<a href=\"" . $script . "?page=" . $prev . $queryURL . "\">&laquo; " . $_ADDONLANG["pageprev"] . " </a>";
            }
            $startPage = 1;
            $endPage = $this->pages;
            if (0 < $this->maxPageLinks && $this->maxPageLinks < $this->pages) {
                $half = (int) floor($this->maxPageLinks / 2);
                $startPage = $this->page - $half;
                $endPage = $this->page + $half;
                if ($startPage < 1) {
                    $startPage = 1;
                    $endPage = $this->maxPageLinks;
                }
                if ($this->pages < $endPage) {
                    $endPage = $this->pages;
                    $startPage = $this->pages - $this->maxPageLinks + 1;
                    if ($startPage < 1) {
                        $startPage = 1;
                    }
                }
            }
            if (1 < $startPage) {
                $links[] = "<a href=\"" . $script . "?page=1" . $queryURL . "\">1</a>";
                if (2 < $startPage) {
                    $links[] = "...";
                }
            }
            for ($j = $startPage; $j <= $endPage; $j++) {
                if ($this->page === $j) {
                    $links[] = "<a style=\"font-weight:bold;\">" . $j . "</a>";
                } else {
                    $links[] = "<a href=\"" . $script . "?page=" . $j . $queryURL . "\">" . $j . "</a>";
                }
            }
            if ($endPage < $this->pages) {
                if ($endPage < $this->pages - 1) {
                    $links[] = "...";
                }
                $links[] = "<a href=\"" . $script . "?page=" . $this->pages . $queryURL . "\">" . $this->pages . "</a>";
            }
            if ($this->page < $this->pages) {
                $next = $this->page + 1;
                $slinks[] = "<a href=\"" . $script . "?page=" . $next . $queryURL . "\"> " . $_ADDONLANG["pagenext"] . " &raquo; </a>";
                if ($this->showFirstAndLast) {
                    $slinks[] = "<a href=\"" . $script . "?page=" . $this->pages . $queryURL . "\"> " . $_ADDONLANG["pagelast"] . " &raquo;&raquo; </a>";
                }
            }
            return implode(" ", $plinks) . implode(" ", $links) . implode(" ", $slinks);
        }
    }
}
