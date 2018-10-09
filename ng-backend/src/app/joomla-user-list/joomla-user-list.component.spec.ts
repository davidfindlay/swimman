import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JoomlaUserListComponent } from './joomla-user-list.component';

describe('JoomlaUserListComponent', () => {
  let component: JoomlaUserListComponent;
  let fixture: ComponentFixture<JoomlaUserListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JoomlaUserListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JoomlaUserListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
