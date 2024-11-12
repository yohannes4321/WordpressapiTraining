<?php

 // written by Yohannes Alemu
class Api_Training_APIs {

    public function __construct() {
          /** i HAVE ADDED A shortcode tag being added is unique and will not conflict with other, already-added shortcode tags
         * IT IS EASY JUST add tag in the $tag like [my_shortcode]
         *  
         */ 
        add_shortcode('my_shortcode', [$this, 'gettinginputformat']);
          /* ihave add*add_action() inserts custom functions into action hooks that execute at specific points */

        /** so as we know add_action it takes 4 paramerters 2 are mandatory and the next two is optioal*/
       /**  the 4 are function addaction(string $hookname,callable $callback ,int prriority=10 ,int accepted_args=1)
        * the third and the fourth is optional
       */
       
 
        add_action('init', [$this, 'senttobackendml']);
    }

    public function gettinginputformat() {
        
        $output = isset($_POST['outputgiven']) ? ($_POST['outputgiven']) : '';

        //i have want start buffering 
        ob_start();
        ?>
        <div id="container"><form method="post"><input type="text"
         name="inputvalue"
          placeholder="enter your idea"  >
<button type="submit" name="submit_button">
    
Enter </button></form>
            <?php if ($output) : ?> <p id="output"><?php echo esc_html($output); ?></p><?php endif; ?>
        </div>
        <?php
            return ob_get_clean();
            //hopefully the function return html block and render the page..
            //ech0 esc_htm to print the output using echo so simple  
    }

    public function senttobackendml() {
        if (isset($_POST['submit_button'])) {
            $input_text = sanitize_text_field($_POST['inputvalue']);
            
            /**wp_remote_post(string $url,array $args=()):array wp Error 
             * i have used wp_remote_post to make http request using post method
             * $url to get retrive  which is our backend ml service url localhost address
             * 
             * body we send the data we get from $ input_text
             *  page loads, we're going to submit some the body 
             * 
            */
        

           
            $result = wp_remote_post('http://http://127.0.0.1:5000/predict', ['method' => 'POST','body'=> json_encode(['text' => $input_text]),'headers'=> [ 'Content-Type' => 'application/json']
            ]);
            // method is post to send the body to the backend ml service by using the url backdend url
            //body is changed ecnode to json file 
            //and the header is tell to server it is json application

            if (is_wp_error($result
            
            )) {
            

                // when we get the responce result if there is error 
                $error= $result->get_error_message();
                $_POST['outputgiven'] = "Error: $error";
            } else {
                $res = wp_remote_retrieve_body($result);// retrive only the body part from result
                $data = json_decode($res, true);
                // parse text to php strucructure from json file format that send from ml service

                if (isset($data['error'])) {$_POST['outputgiven'] = "Error: " . esc_html($data['error']);}
                    // is data is error return the post on the screen the error message
                     else
                
                {
                    #  ihave used esc_html to extract sentiment and confidence from data if data is not error and post outputgiven
                    //which our best ml service backend sendus the responce and extract the sentiment and confidence
            $sentiment = esc_html($data['sentiment']);
            $confidence = esc_html($data['confidence']);
                    $_POST['outputgiven'] = "Sentiment: $sentiment(Confidence: $confidence)";
                }
            }
        }
    }
}

new Api_Training_APIs();
