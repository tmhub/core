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
     * @var boolean
     */
    protected $_filesDispersion = false;

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
     * Set files dispersion flag
     *
     * @param boolean $flag
     */
    public function setFilesDispersion($flag)
    {
        $this->_filesDispersion = $flag;
        return $this;
    }

    /**
     * Get files dispersion flag
     *
     * @return boolean
     */
    public function getFilesDispersion()
    {
        return $this->_filesDispersion;
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
            if (is_array($value) && empty($value['delete'])) {
                $object->setData($dataKey, $value['value']);
            }

            return $this;
        }

        try {
            $uploader = new Varien_File_Uploader($dataKey);
            $uploader->setAllowedExtensions($this->_allowedExtensions)
                ->setFilesDispersion($this->_filesDispersion)
                ->setAllowRenameFiles(true);
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
}
