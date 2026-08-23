<?php

namespace App\Http\Controllers\provider;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
  public function index()
  {
    return view('frontend.provider.dashboard');
  }

  public function request()
  {
    return view('frontend.provider.request');
  }

  public function inprogress()
  {
    return view('frontend.provider.inprogress');
  }

  public function jobDetail()
  {
    return view('frontend.provider.job-detail');
  }

  public function job()
  {
    return view('frontend.provider.job');
  }

  public function upcomingJob()
  {
    return view('frontend.provider.upcoming-job');
  }

  public function inprogressJob()
  {
    return view('frontend.provider.inprogress-job');
  }

  public function jobStart()
  {
    return view('frontend.provider.job-start');
  }

  public function jobInprogress()
  {
    return view('frontend.provider.job-inprogress');
  }

  public function jobNewService()
  {
    return view('frontend.provider.job-new-service');
  }

  public function jobServiceDone()
  {
    return view('frontend.provider.job-service-done');
  }

  public function jobServiceCompleted()
  {
    return view('frontend.provider.service-completed');
  }

  public function earnings()
  {
    return view('frontend.provider.earning');
  }

  public function editProfile()
  {
    return view('frontend.provider.edit-profile');
  }
}
