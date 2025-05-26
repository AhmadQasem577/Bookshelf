<?php
    class Book{
        private $title;
        private $author;
        private $description;
        private $publisher;
        private $publish_date;
        private $post_date;
        private $pdf_content;

        private $image_content;
        
        public function __construct($title, $author, $description, $publisher, $publish_Date, $post_date, $image_content, $pdf_content = null) {
            $this->title = $title;
            $this->author = $author;
            $this->description = $description;
            $this->publisher = $publisher;
            $this->publish_date = $publish_Date;
            $this->post_date = $post_date;
            $this->image_content = $image_content;
            $this->pdf_content = $pdf_content;
        }
         public function getTitle() {
        return $this->title;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPublisher() {
        return $this->publisher;
    }

    public function getPublishDate() {
        return $this->publish_date;
    }

    public function getPostDate() {
        return $this->post_date;
    }

    public function getPdfContent() {
        return $this->pdf_content;
    }

    public function getImageContent() {
        return $this->image_content;
    }
    }
?>