<?php

namespace Vortexgin\FileBrowserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Vortexgin\FileBrowserBundle\Utils\FileUtils;

/**
 * Manager controller class 
 * 
 * @category Controller
 * @package  Vortexgin\FileBrowserBundle\Controller
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/file-browser
 */
class BridgeController extends Controller
{

    /**
     * Function to scan directory
     * 
     * @param string $path Path to directory
     * 
     * @return array
     */
    private function _scandir($path)
    {
        $structure = array(
            'dirs' => array(
                '..' => '../', 
            ), 
            'files' => array(), 
        );
        $finder = new Finder();
        $finder->sortByName();
        $finder->directories()->in($path);
        if ($finder->hasResults()) {
            foreach ($finder as $dir) {
                $structure['dirs'][] = array($dir->getName() => $dir->getRelativePathname());
            }
        }

        $finder->files()->followLinks();
        $finder->files()->in($path);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $structure['dirs'][] = array($file->getName() => $file->getRelativePathname());
            }
        }

        return $sturcture;
    }
    /**
     * File manager page
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function viewBrowseAction(Request $request)
    {
        $get = $request->query->all();
        try {
            $structure = $this->_scandir($this->container->getParameter('vortexgin.file_browser.dir'));

            return $this->render('VortexginFileBrowserBundle:Pages:browse.html.twig', $structure);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return $this->render('VortexginFileBrowserBundle:Pages:error.html.twig', array());
        }
    }

    /**
     * API browse
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function browseAction(Request $request)
    {
        $get = $request->query->all();
        try {
            if (!array_key_exists('prefix', $get) || empty($get['prefix'])) {
                return new JsonResponse('Please specify directory name', 400);
            }
            $prefix = $get['prefix'];
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API mkdir
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function mkdirAction(Request $request)
    {
        $get = $request->query->all();
        try {
            if (!array_key_exists('dirname', $get) || empty($get['dirname'])) {
                return new JsonResponse('Please specify directory name', 400);
            }
            $dirname = $get['dirname'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $fileSystem = new Filesystem();
            $fileSystem->mkdir($path.$dirname, 0700);

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API copy
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function copyAction(Request $request)
    {
        $get = $request->query->all();
        try {
            $force = array_key_exists('force', $get) && !empty($get['force'])?$get['force']:null;

            if (!array_key_exists('file', $get) || empty($get['file'])) {
                return new JsonResponse('Please specify file to copy', 400);
            }
            $file = $get['file'];
            if (!array_key_exists('filename', $get) || empty($get['filename'])) {
                return new JsonResponse('Please specify filename', 400);
            }
            $filename = $get['filename'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $fileSystem = new Filesystem();
            if ($fileSystem->exists($path.$filename) && !$force) {
                return new JsonResponse('New file is exists', 409);
            }
            $fileSystem->copy($path.$file, $path.$filename, true);

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API remove
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function removeAction(Request $request)
    {
        $get = $request->query->all();
        try {
            if (!array_key_exists('file', $get) || empty($get['file'])) {
                return new JsonResponse('Please specify file to remove', 400);
            }
            $file = $get['file'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $fileSystem = new Filesystem();
            $fileSystem->remove(array($path.$file));

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API rename
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function renameAction(Request $request)
    {
        $get = $request->query->all();
        try {
            $force = array_key_exists('force', $get) && !empty($get['force'])?$get['force']:null;

            if (!array_key_exists('file', $get) || empty($get['file'])) {
                return new JsonResponse('Please specify file/dir to rename', 400);
            }
            $file = $get['file'];
            if (!array_key_exists('filename', $get) || empty($get['filename'])) {
                return new JsonResponse('Please specify new name', 400);
            }
            $filename = $get['filename'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $fileSystem = new Filesystem();
            if ($fileSystem->exists($path.$filename) && !$force) {
                return new JsonResponse('New file is exists', 409);
            }
            $fileSystem->rename($path.$file, $path.$filename, true);

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API chmod
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function chmodAction(Request $request)
    {
        $get = $request->query->all();
        try {
            if (!array_key_exists('file', $get) || empty($get['file'])) {
                return new JsonResponse('Please specify file/dir to rename', 400);
            }
            $file = $get['file'];
            if (!array_key_exists('mode', $get) || empty($get['mode'])) {
                return new JsonResponse('Please specify mode', 400);
            }
            $mode = $get['mode'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $fileSystem = new Filesystem();
            $fileSystem->chmod($path.$file, $mode, 0000, true);

            $structure = $this->_scandir($path);

            return new JsonResponse($structure);
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }

    /**
     * API download
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * 
     * @return mixed
     */
    public function downloadAction(Request $request)
    {
        $get = $request->query->all();
        try {
            if (!array_key_exists('file', $get) || empty($get['file'])) {
                return new JsonResponse('Please specify file to download', 400);
            }
            $file = $get['file'];

            $prefix = array_key_exists('prefix', $get) && !empty($get['prefix'])?$get['prefix']:'';
            $path = $this->container->getParameter('vortexgin.file_browser.dir').$prefix;

            $basename = basename($path.$file);
            $ext = FileUtils::get_mime_type($basename);
            $content = file_get_contents($path.$file);

            $response = new Response();
            $response->headers->set('Content-Type', $ext);
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $basename);
            $response->setContent($content);

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse(array(), 417);
        }
    }
}