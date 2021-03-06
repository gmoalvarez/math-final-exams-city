import {Component} from 'angular2/core';
import {SignupFormComponent} from "./signup-form.component";
import {RouteConfig, ROUTER_DIRECTIVES} from "angular2/router";
import {CheckDateComponent} from "./check-date.component";
import { HTTP_PROVIDERS } from 'angular2/http';
import {StudentListComponent} from "./student-list.component";

@Component({
    selector: 'my-app',
    template: `
        <h1>San Diego City College Math Final Exams</h1>
  <nav>
    <button [routerLink]="['Signup']">Sign up</button>
    <button [routerLink]="['CheckDate']">Check/Change date</button>
    <button [routerLink]="['StudentList']">Student List</button>
  </nav>
        <router-outlet></router-outlet>
    `,
    directives: [ROUTER_DIRECTIVES],
    providers: [
        HTTP_PROVIDERS
    ]
})

@RouteConfig([
    {
        path: '/', name: 'Signup', component: SignupFormComponent, useAsDefault: true
    },
    {
        path: '/check-date', name: 'CheckDate', component: CheckDateComponent
    },
    {
        path: '/student-list', name: 'StudentList', component: StudentListComponent
    }
])


export class AppComponent {

}