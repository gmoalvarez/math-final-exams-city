import {Component, OnInit} from "angular2/core";
import {Student} from "./Student";
import {ExamSession} from "./exam-session";
import {DataService} from "./shared/data.service";

@Component({
    selector: 'my-change-date',
    template: `
        <hr>
        <div [hidden]="submitted" class="container">
            <h3>Would you like to change your exam date?</h3>
            <form  class="form" (ngSubmit)="onSubmit()" #changeDateForm="ngForm">
                <div class="form-group">
                    <label for="session">Final Exam Session</label>
                    <select [(ngModel)]="student.examSessionId"
                            ngControl="examSessionId" #examSessionId="ngForm"
                            class="form-control" required>
                        <option *ngFor="#session of sessions"
                                [value]="session.id"
                                [hidden]="session.id==student.examSessionId">
                            <strong>Session: </strong> {{session.id}},
                            <strong>Date: </strong> {{session.date}},
                            <strong>Time: </strong> {{session.time}},
                            <span class="label-default label">{{session.seatsAvailable}} seats available</span>
                        </option>
                    </select>
                    <div [hidden]="examSessionId.valid || examSessionId.pristine"
                         class="alert alert-danger">
                        Exam Session is required
                    </div>
                </div>
                <br>
                <button [disabled]="!changeDateForm.form.valid"
                        type="submit" class="btn btn-default"
                        >Submit
                </button>
            </form>
        </div>

        <!--Confirmation Message-->
    <div [hidden]="!submitted">
        <h2>You changed your exam date to the following:</h2>
        <div class="row">
            <div class="col-xs-3">First Name</div>
            <div class="col-xs-9  pull-left">{{ student.firstName }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Last Name</div>
            <div class="col-xs-9  pull-left">{{ student.lastName }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">CSID</div>
            <div class="col-xs-9 pull-left">{{ student.id }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Exam Session id</div>
            <div class="col-xs-9 pull-left">{{ student.examSessionId }}</div>
        </div>
        <!--<div class="row">-->
            <!--<div class="col-xs-3">Date</div>-->
            <!--<div class="col-xs-9 pull-left">{{ currentSession.dateTime }}</div>-->
        <!--</div>-->
        <div class="row">
            <div class="col-xs-3">CRN</div>
            <div class="col-xs-9 pull-left">{{ student.crn }}</div>
        </div>

    </div>
    `,
    providers: [DataService],
    directives: [],
    inputs: ['student:checkDateStudent','currentSession:oldSession']
})

export class ChangeDateComponent implements OnInit{

    ngOnInit() { this.getSessions()}

    constructor(private dataService: DataService) {}

    student = new Student('', '', '', '', '');
    currentSession: ExamSession;
    errorMessage: string;
    submitted = false;
    found = false;
    sessions: ExamSession[];

    getSessions() {
        this.dataService.getExamSessions()
            .subscribe(
                examSessions => {this.sessions = examSessions;
                    console.log(examSessions);
                },
                error => this.errorMessage = error
            )
    }

    //Note that the student object carries the new exam session id as a property that
    //is bound to the form.
    changeStudent (student: Student) {
        console.log('Changing student time');
        console.log(student);
        console.log('The old exam session is');
        console.log(this.currentSession);
        this.dataService.addStudent(student, 'change')
            .subscribe(
                student => {//only contains studentId and examSessionId
                    console.log('student is');
                    console.log(student);
                    this.getSessions();
                    this.student.examSessionId = student.examSessionId;
                    this.currentSession = <ExamSession>this.sessions.filter(session=>session.id == student.examSessionId)[0];
                    console.log('The new current session is ');
                    console.log(this.currentSession);
                    //TODO: This returns an array with a single element. fix it
                },
                error => this.errorMessage = <any>error
            );
    }

    onSubmit() {
        this.submitted = true;
        this.changeStudent(this.student);
    }

}