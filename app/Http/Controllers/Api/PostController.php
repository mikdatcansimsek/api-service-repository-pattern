<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Http\Resources\PostCollection;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get all posts",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="published",
     *         in="query",
     *         description="Filter published posts",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="latest",
     *         in="query",
     *         description="Get latest posts (limit)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of posts",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Post")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // PERFORMANCE OPTIMIZATION: User interactions ile birlikte al
        $userId = Auth::id();
        
        if ($request->has('page')) {
            // Paginated response with user interactions
            $posts = $this->postService->getPaginatedPostsWithUserInteractions(
                $request->get('per_page', 15), 
                $userId
            );
            return PostResource::collection($posts);
        }

        // Regular collection with user interactions
        $posts = match (true) {
            $request->has('published') => $this->postService->getPublishedPosts(),
            $request->has('category_id') => $this->postService->getPostsByCategory($request->category_id),
            $request->has('user_id') => $this->postService->getPostsByUser($request->user_id),
            $request->has('search') => $this->postService->searchPosts($request->search),
            $request->has('latest') => $this->postService->getLatestPosts($request->get('latest', 10)),
            default => $this->postService->getPostsWithUserInteractions($userId),
        };

        return PostResource::collection($posts);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create post",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content","user_id","category_id"},
     *             @OA\Property(property="title", type="string", example="My First Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of my first post"),
     *             @OA\Property(property="excerpt", type="string", example="Short description"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="is_published", type="boolean", example=false),
     *             @OA\Property(property="published_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $post = $this->postService->createRecord($request->all());

        return new PostResource($post);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Get post by ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function show(int $id)
    {
        // PERFORMANCE OPTIMIZATION: User interactions ile birlikte tek post al
        $userId = Auth::id();
        $post = $this->postService->getPostWithUserInteractions($id, $userId);

        return new PostResource($post);
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="Update post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Post Title"),
     *             @OA\Property(property="content", type="string", example="Updated content"),
     *             @OA\Property(property="excerpt", type="string", example="Updated excerpt"),
     *             @OA\Property(property="is_published", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $post = $this->postService->updateRecord($id, $request->all());

        return new PostResource($post);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Post deleted"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $this->postService->deleteRecord($id);

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/api/posts/slug/{slug}",
     *     summary="Get post by slug",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post found",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function findBySlug(string $slug)
    {
        $post = $this->postService->findPostBySlug($slug);

        return new PostResource($post);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{id}/publish",
     *     summary="Publish post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post published",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function publish(int $id)
    {
        $post = $this->postService->publishPost($id);

        return new PostResource($post);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/{id}/unpublish",
     *     summary="Unpublish post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post unpublished",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     )
     * )
     */
    public function unpublish(int $id)
    {
        $post = $this->postService->unpublishPost($id);

        return new PostResource($post);
    }
}
