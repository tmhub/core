<?php

/**
 * Model to upload images into <margento_root>/media
 */
class TM_Core_Model_Image_Uploader
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    /**
     * @var array
     */
    protected $_allowedExtensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

    /**
     * @var string
     */
    protected $_directory = 'tmcore';

    /**
     * Get sub-directry name where file will be uploaded
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }

    /**
     * Set sub-directry name where file will be uploaded
     *
     * @param string $dirName
     * @return $this
     */
    public function setDirectory($dirName)
    {
        $this->_directory = $dirName;
        return $this;
    }

    /**
     * Get target path for upload file
     *
     * @return string
     */
    public function getTargetPath()
    {
        $path = Mage::getBaseDir('media') . DS . $this->getDirectory();
        if (!file_exists($path)) {
            $result = $this->createDirectory(
                $this->getDirectory(),
                Mage::getBaseDir('media')
            );
            $path = $result['path'];
        }

        return $path;
    }

    /**
     * Get allowed file extension
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->_allowedExtensions;
    }

    /**
     * Upload image
     *
     * @param  Varien_Object $object
     * @param  string $dataKey
     * @return $this
     */
    public function upload(Varien_Object $object, $dataKey)
    {
        $value = $object->getData($dataKey);
        if (is_array($value) && !empty($value['delete'])) {
            $this->delete($value['value']);
            $object->setData($dataKey, '');
        }

        if (empty($_FILES[$dataKey]['name'])) {
            if (is_array($value)) {
                $object->setData($dataKey, $value['value']);
            }

            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader($dataKey);
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $uploader->setAllowRenameFiles(true);
            if (@class_exists('Mage_Core_Model_File_Validator_Image')) {
                $uploader->addValidateCallback(
                    Mage_Core_Model_File_Validator_Image::NAME,
                    Mage::getModel('core/file_validator_image'),
                    'validate'
                );
            }
            $uploader->save($this->getTargetPath());
            $object->setData($dataKey, $uploader->getUploadedFileName());
        } catch (Exception $e) {
            $object->unsData($dataKey);
            throw $e;
        }

        return $this;
    }

    /**
     * Delete image
     *
     * @param  string $imageName
     * @return $this
     */
    public function delete($imageName)
    {
        $path = $this->getTargetPath();
        @unlink($path . DS . $imageName);
        return $this;
    }

    /**
     * Create new directory in storage
     * Inspired by Mage_Cms_Model_Wysiwyg_Images_Storage::createDirectory
     *
     * @param string $name New directory name
     * @param string $path Parent directory path
     * @throws Mage_Core_Exception
     * @return array New directory info
     */
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            Mage::throwException(Mage::helper('cms')->__('Invalid folder name. Please, use alphanumeric characters, underscores and dashes.'));
        }

        if (!is_dir($path) || !is_writable($path)) {
            $path = Mage::getBaseDir('media');
        }

        $newPath = $path . DS . $name;

        if (file_exists($newPath)) {
            Mage::throwException(Mage::helper('cms')->__('A directory with the same name already exists. Please try another folder name.'));
        }

        $io = new Varien_Io_File();
        if ($io->mkdir($newPath)) {
            $result = array(
                'name'          => $name,
                'path'          => $newPath
            );
            return $result;
        }

        Mage::throwException(Mage::helper('cms')->__('Cannot create new directory.'));
    }
}
