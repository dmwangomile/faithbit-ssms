<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        
        // Verb filter
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
                'refresh' => ['POST'],
                'logout' => ['POST'],
            ],
        ];
        
        // Authentication filter (exclude login and refresh)
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login', 'refresh', 'options'],
        ];
        
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($action->id === 'options') {
            return true;
        }
        
        return parent::beforeAction($action);
    }

    /**
     * Login action
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;
        $username = $request->post('username');
        $password = $request->post('password');
        
        if (!$username || !$password) {
            return $this->errorResponse('Username and password are required', 400);
        }
        
        // Find user by username or email
        $user = User::findByUsername($username) ?: User::findByEmail($username);
        
        if (!$user || !$user->validatePassword($password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }
        
        if ($user->status !== User::STATUS_ACTIVE) {
            return $this->errorResponse('Account is not active', 403);
        }
        
        // Generate tokens
        $accessToken = $user->generateAccessToken();
        $refreshToken = $user->generateRefreshToken();
        
        // Update last login
        $user->updateLastLogin();
        
        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->getFullName(),
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                    'code' => $user->branch->code,
                ] : null,
                'permissions' => $user->getPermissions(),
            ],
            'tokens' => [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => Yii::$app->params['jwt']['expiration'],
            ],
        ], 'Login successful');
    }

    /**
     * Refresh token action
     */
    public function actionRefresh()
    {
        $request = Yii::$app->request;
        $refreshToken = $request->post('refresh_token');
        
        if (!$refreshToken) {
            return $this->errorResponse('Refresh token is required', 400);
        }
        
        try {
            $jwtParams = Yii::$app->params['jwt'];
            $decoded = JWT::decode($refreshToken, new Key($jwtParams['key'], $jwtParams['algorithm']));
            
            if ($decoded->type !== 'refresh') {
                return $this->errorResponse('Invalid token type', 400);
            }
            
            $user = User::findOne(['id' => $decoded->uid, 'status' => User::STATUS_ACTIVE]);
            
            if (!$user || $user->refresh_token !== $refreshToken) {
                return $this->errorResponse('Invalid refresh token', 401);
            }
            
            // Generate new tokens
            $accessToken = $user->generateAccessToken();
            $newRefreshToken = $user->generateRefreshToken();
            
            return $this->successResponse([
                'tokens' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $newRefreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $jwtParams['expiration'],
                ],
            ], 'Token refreshed successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Invalid refresh token', 401);
        }
    }

    /**
     * Logout action
     */
    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        
        if ($user) {
            $user->revokeTokens();
        }
        
        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Handle OPTIONS requests
     */
    public function actionOptions()
    {
        return '';
    }

    /**
     * Success response
     */
    protected function successResponse($data = null, $message = 'Success')
    {
        Yii::$app->response->statusCode = 200;
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Error response
     */
    protected function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        Yii::$app->response->statusCode = $code;
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}