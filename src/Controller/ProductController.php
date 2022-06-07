<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pimcore\Controller\FrontendController;
use App\Model\Tank;
use App\Model\Rate;
use Pimcore\Model\DataObject\Rate\Listing;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Carbon\Carbon;
use App\Model\Helper;

class ProductController extends FrontendController
{

    public function defaultAction(Request $request, PaginatorInterface $paginator)
    {
        $tankList = Tank::getAllTanks();
        foreach ($tankList as $item) {
            $actual = Carbon::parse($item->getDate_time());
            $now = Carbon::parse(Carbon::now());

        }
        $paginator = $paginator->paginate(
            $tankList,
            $request->get('page', 1),
            3
        );

        return $this->render('product/product.html.twig', [
            'paginator' => $paginator,
            'paginationVariables' => $paginator->getPaginationData()
        ]);;
    }

    /**
     * @Route("/auction/detail/{identification}", defaults={"identification"=""})
     *
     */
    public function detailAction (Request $request, $identification)
    {
        /*
         * Form for rate
         */
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();

        $form = $formFactory->createBuilder()
            ->add('user', TextType::class)
            ->add('email', EmailType::class)
            ->add('bid', IntegerType::class)
            ->getForm();
        $request = Request::createFromGlobals();
        $form->handleRequest($request);


        $tankObject = Tank::getTankById($identification);
                /*
         * Render tank details
         * Listing() of rates
         */
        $ratesForTank = new Listing();
        $ratesForTank = $ratesForTank->setCondition("tank_id LIKE ?", [$identification] );



        if ($form->isSubmitted() && $form->isValid()) {

            $dataRate = $form->getData();

            $errorMessage = array();

            if (Rate::validateFields($dataRate, $ratesForTank, $errorMessage) === true) {
                $dateBid = new Carbon();
                $currentTime = time();
                $dateBid = $dateBid->setTimestamp($currentTime);

                /* Create rate object from form data */
                $newRate = new Rate();
                $newRate->setKey(
                    \Pimcore\Model\Element\Service::getValidKey(
                        $tank_id = $identification . '_' . $currentTime,
                        'object'
                    )
                );
                $newRate->setParentId(5);
                $newRate->setUser($dataRate['user'])
                    ->setEmail($dataRate['email'])
                    ->setBid($dataRate['bid'])
                    ->setTank_id($identification)
                    ->setDate($dateBid);
                $newRate->setPublished(true);
                $newRate->save();
            }else {
                $errorMessage[] = Rate::validateFields($dataRate, $ratesForTank, $errorMessage);
            }
            if (!empty($errorMessage)) {
                $error = $errorMessage[0];
            }else {
                $error = '';
            }

            $response = new RedirectResponse('/auction/detail/'. $identification . '?error=' . $error);
            $response->prepare($request);

            return $response->send();
        }
        if (isset($_GET['error'])) {
            $errorMessage = $_GET['error'];
        }
        else {
            $errorMessage = array();
        }



        return $this->render('product/product-detail.html.twig', [
            'tank' => $tankObject,
            'allRates' => $ratesForTank,
            'errorMessage' => $errorMessage,
            'form' => $form->createView(),
        ]);;
    }
    /**
     * @Route("/test")
     */
    public function testAction(Request $request)
    {
        $this->disableViewAutoRender($request);
    }
}
