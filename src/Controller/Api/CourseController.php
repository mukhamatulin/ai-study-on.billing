<?php

namespace App\Controller\Api;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Service\CurrentUserResolver;
use App\Service\PaymentService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/courses')]
class CourseController extends AbstractController
{
    #[Route('', name: 'api_courses_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): JsonResponse
    {
        return $this->json(array_map([$this, 'serializeCourse'], $courseRepository->findBy([], ['title' => 'ASC'])));
    }

    #[Route('/{code}', name: 'api_courses_show', methods: ['GET'])]
    public function show(string $code, CourseRepository $courseRepository): JsonResponse
    {
        $course = $courseRepository->findOneBy(['code' => $code]);
        if (!$course) {
            return $this->json(['code' => 404, 'message' => 'Курс не найден'], 404);
        }

        return $this->json($this->serializeCourse($course));
    }

    #[Route('/{code}/pay', name: 'api_courses_pay', methods: ['POST'])]
    public function pay(
        string $code,
        Request $request,
        CourseRepository $courseRepository,
        CurrentUserResolver $currentUserResolver,
        PaymentService $paymentService,
    ): JsonResponse {
        $course = $courseRepository->findOneBy(['code' => $code]);
        if (!$course) {
            return $this->json(['code' => 404, 'message' => 'Курс не найден'], 404);
        }

        $user = $currentUserResolver->resolve($request);

        try {
            $transaction = $paymentService->pay($user, $course);
        } catch (RuntimeException $exception) {
            return $this->json(['code' => 406, 'message' => $exception->getMessage()], 406);
        }

        return $this->json([
            'success' => true,
            'course_type' => $course->getType(),
            'expires_at' => $transaction->getExpiresAt()?->format(DATE_ATOM),
        ]);
    }

    private function serializeCourse(Course $course): array
    {
        $data = [
            'code' => $course->getCode(),
            'title' => $course->getTitle(),
            'type' => $course->getType(),
        ];

        if ($course->getType() !== Course::TYPE_FREE) {
            $data['price'] = $course->getPrice();
        }

        return $data;
    }

}
