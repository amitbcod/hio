<?php
class GlobalRepository
{
    use UsesRestAPI;
   public static function get_custom_variable($identifier)
    {
        $custom_variables = self::get_custom_variables();

        // Convert to array if it's object (to prevent the array-access error)
        if (is_object($custom_variables)) {
            $custom_variables = (array) $custom_variables;
        }

        return (object) [
            'statusCode' => 200,
            'is_success' => true,
            'custom_variable' => $custom_variables[$identifier] ?? null
        ];
    }

    public static function get_custom_variables()
    {
        static $result;

        if (isset($result)) {
            return $result;
        }

        $api_result = self::get_method('/webshop/get_custom_variables');

        // Check if valid object and custom_variables exists
        if (!is_object($api_result) || !isset($api_result->custom_variables)) {
            log_message('error', 'custom_variables is not an array or is null');
            return [];
        }

        // Decode it into an array if needed
        $custom_vars = $api_result->custom_variables;

        if (!is_array($custom_vars)) {
            log_message('error', 'custom_variables is not an array even after isset check');
            return [];
        }

        // Now build result array indexed by identifier
        $result = array_combine(
            array_column($custom_vars, 'identifier'),
            $custom_vars
        );

        return $result;
    }

    public static function get_fbc_users_shop(){
        static $result;

        if(isset($result)){
            return $result;
        }
        
return $result = self::get_method('/webshop/fbc_users_shop');
    }

    public static function get_theme($shopcode, $shop_id){  
        $result = self::get_method('/webshop/get_theme/'.$shopcode, 0);        
        return $result;
    }
}
