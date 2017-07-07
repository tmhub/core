<?php

class TM_Core_Model_File_Validator_Image extends Mage_Core_Model_File_Validator_Image
{
    /**
     * Override original method to prevent destroying transparacy of images
     */
    public function validate($filePath)
    {

        $justCallOrigin = version_compare(Mage::getVersion(), '1.9.3.3', '<');

        if ($justCallOrigin) {
            // call parent method if magento version is lower then 1.9.3.3
            return parent::validate($filePath);
        }

        $fixImage = array(
                IMAGETYPE_PNG => 'imagepng',
                IMAGETYPE_GIF => 'imagegif'
            );
        list($imageWidth, $imageHeight, $fileType) = getimagesize($filePath);

        if (isset($fixImage[$fileType])) {
            // get original image
            $image = imagecreatefromstring(file_get_contents($filePath));
        }

        $result = parent::validate($filePath);

        if (isset($fixImage[$fileType])) {
            // fix transparency for validated image
            $img = imagecreatetruecolor($imageWidth, $imageHeight);
            imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
            imagealphablending($img, false);
            imagesavealpha($img, true);
            imagecopyresampled($img, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
            call_user_func($fixImage[$fileType], $img, $filePath);
        }

        return $result;

    }

}
