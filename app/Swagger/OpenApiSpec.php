<?php
namespace App\Swagger;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Jacklap",
 *     description="API Documentation"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Enter your bearer token from login",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 *
 * @OA\Server(
 *     url="https://api.jacklap.ca/api",
 *     description="Jacklap API Local Server"
 * )
 */

class OpenApiSpec
{}
