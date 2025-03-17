<?php
declare(strict_types=1); 
namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table            = 'refresh_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    =  ['user_id', 'token', 'expires_at','updated_at','created_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

     // Periksa validitas refresh token
     public function isValid(string $token):bool
     {
         $tokenData = $this->where('token', $token)->first();
         if (!$tokenData) {
             return false;
         }
 
         return strtotime($tokenData['expires_at']) > time();
     }
 
     // Hapus token yang sudah kedaluwarsa
     public function deleteExpiredTokens():void
     {
         $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
     }
}
