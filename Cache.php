<?php

    class Cache {

        private $buffer;
        private $dirname;
        private $duration;


        /**
         * @param string $dirname Chemin où sera placé le cache
         * @param int $duration Temps de vie en minute du cache
         *
         * @return void
         */
        public function __construct($dirname, $duration) {
            $this->dirname = $dirname;
            $this->duration = $duration;
        }


        /**
         * @param string $filename Nom du fichier de cache
         * @param string $content Contenu du cache
         *
         * @return boolean Cette fonction retourne TRUE en cas de succès ou FALSE si une erreur survient
         */
        public function write($filename, $content) {
            return file_put_contents($this->dirname . DIRECTORY_SEPARATOR . $filename, $content);
        }


        /**
         * @param string $filename Nom du fichier de cache
         *
         * @return string Retourne le contenu du cache si le temps de vie est valide, ou FALSE si le temps est dépassé
         */
        public function read($filename) {
            if(!file_exists($this->dirname . DIRECTORY_SEPARATOR . $filename)) {
                return false;
            }

            $lifetime = (time() - filemtime($this->dirname . DIRECTORY_SEPARATOR . $filename)) / 60;

            if($lifetime > $this->duration) {
                return false;
            }
            return file_get_contents($this->dirname . DIRECTORY_SEPARATOR . $filename);
        }


        /**
         * @param string $filename Nom du fichier de cache à supprimer
         *
         * @return void
         */
        public function delete($filename) {
            if(file_exists($this->dirname . DIRECTORY_SEPARATOR . $filename)) {
                unlink($this->dirname . DIRECTORY_SEPARATOR . $filename);
            }
        }


        /**
         * Supprime tous le cache
         *
         * @return void
         */
        public function clear() {
            $files = scandir($this->dirname . DIRECTORY_SEPARATOR);

            foreach($files as $file) {
                if(is_file($this->dirname . DIRECTORY_SEPARATOR . $file)) {
                    unlink($this->dirname . DIRECTORY_SEPARATOR . $file);
                }
            }
        }


        /**
         * @param string $file Fichier à inclure
         * @param string $cachename Nom du fichier de cache
         *
         * @return true
         */
        public function inc($file, $cachename = null) {
            if(!$cachename) {
                $cachename = basename($file);
            }

            if($content = $this->read($cachename)) {
                echo $content;
                return true;
            }

            ob_start();
            require $file;
            $content = ob_get_clean();
            $this->write($cachename, $content);
            echo $content;
            return true;
        }


        /**
         * Démarre la mise en cache
         *
         * @param string $cachename Nom du fichier de cache
         *
         * @return true
         */
        public function start($cachename) {
            if($content = $this->read($cachename)) {
                echo $content;
                $this->buffer = false;
                return true;
            }
            ob_start();
            $this->buffer = $cachename;
            return true;
        }


        /**
         * Stop la mise en cache
         *
         * @return boolean
         */
        public function end() {
            if($this->buffer) {
                return false;
            }

            $content = ob_get_clean();
            echo $content;
            $this->write($this->buffer, $content);
            return true;
        }
    }
