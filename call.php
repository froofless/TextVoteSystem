<?php

// Greet the caller
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
  <Say voice="woman">
    Thank you for calling. Unfortunately, you can only vote by text. Have a great evening!
  </Say>
</Response>
