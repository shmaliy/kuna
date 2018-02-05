<?php

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'Login';
?>
<div class="site-index">
    <h1><?=$this->title; ?></h1>

    <form method="post">
        <div class="form-group">
            <label for="exampleInputEmail1">Email address</label>
            <input type="email" class="form-control" name="email" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
            <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" class="form-control" name="password" id="exampleInputPassword1" placeholder="Password">
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
